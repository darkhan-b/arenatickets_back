<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StandardEloquentExport;
use App\Http\Controllers\Controller;
use App\Models\General\Spacemedia;
use Illuminate\Http\Request;
use App\Models\General\EloquentHandler;
use File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class EloquentController extends Controller {

    public function eloquentIndex($model, Request $request) {
        $model_config = EloquentHandler::getModelConfig($model);
        $class_name = $model_config["path"];
        if(!$this->checkIfModelExists($model, $class_name)) {
            abort(404);
        }
        if($request->all_for_select) {
            return json_encode($class_name::pluck($request->title_for_select ? $request->title_for_select : 'title','id'));
        }
        $select_data = [];
        foreach($model_config['fields'] as $f) { // collecting dropdowns on table for filtering
            if(isset($f['table_filter_select']) && $f['table_filter_select']) {
                $c_name = $f['select_data']['model'] ?? null;
                if($c_name) $select_data[$f['id']] = $c_name::select(is_array($f['select_data']['title']) ? $f['select_data']['title'][0] : $f['select_data']['title'],'id')->get();
            }
        }
        $sort = isset($request->sort) ? $request->sort : $model_config["initial_order"][0];
        $order = isset($request->order) ? $request->order : $model_config["initial_order"][1];

        $query = $class_name::orderBy($sort,$order);
        if($model  == 'deleted_order') $query->onlyTrashed();

        $search = [];
        if(isset($request->search)) {
            parse_str($request->search,$search);
        }

        foreach($search as $key => $val) {
            if(!$val) { continue; }
            if(isset($model_config["fields"][$key]["skipsearch"])) { continue; }
            if($val === 'false') { $val = 0; }
            if($val === 'true') { $val = 1; }
            if(isset($model_config["fields"][$key]["likesearch"]) && $model_config["fields"][$key]["likesearch"]) {
                $query->whereRaw("LOWER($key) LIKE ?", ['%' . mb_strtolower($val) . '%']);
            } else {
                $query->whereRaw("LOWER($key) = ?", [$val]);
            }
        }

        if(method_exists($class_name,'additionalSearchQuery')) {
            $query = $class_name::additionalSearchQuery($query,$search);
        }

        if(isset($model_config["with"]) && $model_config["with"]) {
            $query->with($model_config["with"]);
        }

        if(method_exists($class_name,'additionalListQuery')) {
            $query = $class_name::additionalListQuery($query);
        }

        if($request->exportToExcel) {
            return $query->get();
        }

        $data = $query->paginate(isset($request->paginate) ? $request->paginate : 30);

        $result = [];
        $result['list'] = $data;
        $result['select_data'] = $select_data;
        $result['total'] = $data->total();
        return response()->json($result);
    }


    public function eloquentAutocomplete($model, Request $request, $field = 'title', $organizer_id = null) {
        $model_config = EloquentHandler::getModelConfig($model);
        if(!$this->checkIfModelExists($model, $model_config['path'])) {
            abort(404);
        }
        $class_name = $model_config["path"];

        $q = $class_name::selectRaw('id, '.$field.' as text')->orderBy($field,'asc');

        if($request->ids) {
            $ids = is_array($request->ids) ? $request->ids : explode(',', $request->ids);
            if(in_array($model, ['venue', 'category', 'show'])) {
                $q = $class_name::selectRaw('id, CONCAT("[", id, "] ", JSON_EXTRACT(title,"$.ru")) as text')->orderBy($field,'asc');
            }
            if(in_array($model, ['timetable'])) {
                return json_encode($class_name::withoutGlobalScopes()->selectRaw('timetables.id, CONCAT("[", timetables.id, "] ", date, " - ", JSON_EXTRACT(shows.title,"$.ru")) as text')
                    ->where('timetables.client_id', clientId())
                    ->join('shows','timetables.show_id','=','shows.id')
                    ->whereIn('timetables.id', $ids)
                    ->orderBy($field,'asc')
                    ->get());
            }
            return json_encode($q->whereIn('id', $ids)->take(10)->get());
        }
        if($request->id) {
            return json_encode([$q->where('id',$request->id)->first()]);
        }
        if($model == 'timetable') {
            $q = $class_name::withoutGlobalScopes()
                ->selectRaw('timetables.id, CONCAT("[", timetables.id, "] ", date, " - ", JSON_EXTRACT(shows.title,"$.ru")) as text')
                ->where('timetables.client_id', clientId())
                ->orderBy($field, 'asc')
                ->join('shows','timetables.show_id','=','shows.id')
                ->whereHas('show', function($qry) use($request) {
                    $qry->whereRaw('LOWER(title->"$.ru") like ?', ['%'.mb_strtolower($request->search).'%'])->select('id', 'title');
                });
            if($organizer_id) {
                $q->whereHas('show', function($qry) use($organizer_id) {
                    $qry->where('organizer_id', $organizer_id);
                });
            }
            $res = $q->take(20)->get();
        } else if(in_array($model, ['venue', 'category', 'show', 'venue_scheme'])) {
            $q = $class_name::selectRaw('id, CONCAT("[", id, "] ", JSON_EXTRACT(title,"$.ru")) as text')
                ->orderBy($field,'asc')
                ->whereRaw('LOWER(title->"$.ru") like ?', ['%'.mb_strtolower($request->search).'%']);
            if($model === 'show' && $organizer_id) {
                $q->where('organizer_id', $organizer_id);
            }
            $res = $q->take(20)->get();
        } else {
            $res = $q->where($field,'LIKE', '%' . $request->search . '%')->take(20)->get();
        }
        return json_encode($res);
    }


    /// *** Launched to gather data for creation form *** ///
    public function eloquentAdd($model, Request $request) {
        $model_config = EloquentHandler::getModelConfig($model);
        $class_name = $model_config["path"];
        $obj = method_exists($class_name, 'customPrepareModel') ? $class_name::customPrepareModel($request->all()) : new $class_name();
        $select_models = method_exists($class_name, 'customSelectOptions') ? $class_name::customSelectOptions($request->all()) : EloquentHandler::addSelectOptions($model_config);
        $this->clearCaches($model_config);
        return json_encode([
            'object' => $obj,
            'select_models' => $select_models,
            'media' => []
        ]);
    }



    public function clearCaches($model_config) {
        if(isset($model_config["clearcache"])) {
            foreach($model_config["clearcache"] as $cache) {
                Cache::forget($cache);
            }
        }
    }

    public function eloquentGet($model, $id) {
        $model_config = EloquentHandler::getModelConfig($model);
        $class_name = $model_config["path"];
        if(method_exists($class_name, 'customDetails')) {
            $obj = $class_name::customDetails($id);
        } else {
            $obj = $class_name::findOrFail($id);
        }
        return json_encode($obj);
    }

    public function eloquentStore($model, Request $request) {
        $model_config = EloquentHandler::getModelConfig($model);
        $class_name = $model_config["path"];
        $this->classValidate($request, $model, 'create', $class_name);
        EloquentHandler::checkPermissions($model_config,"create");
        if(method_exists($class_name,'customCreate')) {
            $obj = $class_name::customCreate($request);
        } else {
            $obj = $class_name::create($request->all());
        }
        $this->eloquentCheckImages($obj, $request);
        $this->clearCaches($model_config);
        $obj = $this->loadEloquentRelations($class_name, $model_config, $obj->id);
        return json_encode($obj);
    }



    public function eloquentEdit($model, $id) {
        $model_config = EloquentHandler::getModelConfig($model);
        $class_name = $model_config["path"];
        $obj = $this->loadEloquentRelations($class_name, $model_config, $id);
        $media = EloquentHandler::getMediaTeasers($model_config,$obj);
        $select_models = EloquentHandler::addSelectOptions($model_config);
        return json_encode(['object' => $obj, 'select_models' => $select_models, 'media' => $media]);
    }



    public function eloquentUpdate($model, $id, Request $request) {
        $model_config = EloquentHandler::getModelConfig($model);
        $class_name = $model_config["path"];
        $obj = $class_name::findOrFail($id);
        if(!$request->_source || $request->_source != 'useradminpage') {
            $this->classValidate($request, $model, 'update', $class_name, $obj);
        }
        EloquentHandler::checkPermissions($model_config,"update", $obj);
        if(method_exists($obj,'customUpdate')) {
            $obj->customUpdate($request);
        } else {
            $obj->update($request->all());
        }
        $this->eloquentCheckImages($obj, $request);
        $this->clearCaches($model_config);
        $obj = $this->loadEloquentRelations($class_name, $model_config, $id);
        return json_encode($obj);
    }

    public function eloquentUpdatePart($model, $id, Request $request) {
        $model_config = EloquentHandler::getModelConfig($model);
        $class_name = $model_config["path"];
        $obj = $class_name::findOrFail($id);
        EloquentHandler::checkPermissions($model_config,"update", $obj);
        $obj->update($request->all());
        $this->clearCaches($model_config);
        return json_encode(true);
    }


    public function eloquentClone($model, $id, Request $request) {
        $model_config = EloquentHandler::getModelConfig($model);
        $class_name = $model_config["path"];
        $obj = $class_name::findOrFail($id);
        if(method_exists($obj,'customReplicate')) {
            $new_obj = $obj->customReplicate();
        } else {
            $new_obj = $obj->replicate();
            $new_obj->push();
        }
        $new_obj = $this->loadEloquentRelations($class_name, $model_config, $new_obj->id);
        return json_encode($new_obj);
    }




    public function loadEloquentRelations($class_name, $model_config, $id) {
        if(method_exists($class_name, 'customShow')) {
            $obj = $class_name::customShow($id);
        } else {
            $obj = $class_name::findOrFail($id);
        }
        if(isset($model_config["with"]) && $model_config["with"]) {
            foreach($model_config["with"] as $w) {
                $w = explode(":", $w);
                $obj->{$w[0]} = $obj->{$w[0]};
            }
        }
        return $obj;
    }



    public function eloquentView($model, $id) {
        $model_config = EloquentHandler::getModelConfig($model);
        $class_name = $model_config["path"];
        $obj = $class_name::findOrFail($id);
        return response()->json(view('eloquent.custom.'.$model.'.view',compact('model','model_config','obj','id'))->render());
    }



    public function eloquentDestroy($model, $id, Request $request) {
        $model_config = EloquentHandler::getModelConfig($model);
        $class_name = $model_config["path"];
        if($model === 'deleted_order') {
            $obj = $class_name::onlyTrashed()->findOrFail($id);
            if(Auth::user()->can('force_delete_order')) $obj->fullDelete();
        } else {
            $obj = $class_name::findOrFail($id);
            $obj->delete();
        }
        return response()->json(true);
    }


    public function eloquentDestroyBulk($model, Request $request) {
        $model_config = EloquentHandler::getModelConfig($model);
        $class_name = $model_config["path"];
        foreach($request->ids as $id) {
            $obj = $class_name::findOrFail($id);
            $obj->delete();
        }
    }

    public function eloquentCheckImages($object, $request) {
        if(in_array('App\\Traits\\AnimatedMedia',class_uses(get_class($object)))) {
            $object->saveImage($request, false);
        }
    }


    public function eloquentConfig($model) {
        $model_config = EloquentHandler::getModelConfig($model);
        return json_encode($model_config);
    }


    public function checkIfModelExists($model, $modelpath = null) {
        if($modelpath && class_exists($modelpath)) return true;
        $name = Str::studly($model);
        return class_exists(('App\\Models\\'.$name));
    }


    public function mediaDelete(Request $request) {
        $media = Spacemedia::find($request->id);
        $model = $media->model;
        if($model->store_primary_image && $model->primary_media_id == $media->id) {
            $model->update([
                'primary_media_id' => null,
                'primary_media_ext' => null
            ]);
        }
        $media->delete();
    }


    public function mediaMove($model, $id, Request $request) {
        $model_config = EloquentHandler::getModelConfig($model);
        $class_name = $model_config["path"];
        $obj = $this->loadEloquentRelations($class_name, $model_config, $id);
        $media = Spacemedia::find($request->id);

        $object_media = Spacemedia::where('model_type',$media->model_type)
            ->where('model_id',$media->model_id)->orderBy('sort_order','desc')->get();
        $sort = count($object_media);
        $buffer = null;
        foreach($object_media as $i => $m) {
            if($m->id == $media->id && $i != (count($object_media)-1)) {
                $buffer = $m;
                continue;
            }
            $m->sort_order = $sort;
            $m->save();
            $sort--;
            if($buffer) {
                $buffer->sort_order = $sort;
                $buffer->save();
                $buffer = null;
                $sort--;
            }

        }

        $media = EloquentHandler::getMediaTeasers($model_config,$obj);
        return json_encode($media);
    }

    public function exportToExcel($model, Request $request) {
        $request->merge(['exportToExcel' => true]);
        $data = $this->eloquentIndex($model, $request);
        $export = new StandardEloquentExport($data->toArray());
        return Excel::download($export, 'data.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }

    private function classValidate($request, $model, $operation = 'create', $class_name, $obj = null) {
        $request->validate(EloquentHandler::getModelValidationRules($model,$operation));
        if(method_exists($class_name, 'customValidationOn'.$operation)) {
            call_user_func([$class_name, 'customValidationOn'.$operation], $request, $obj);
        }
    }



}
