<?php

namespace App\Models\General;

use App\Models\Specific\Show;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EloquentHandler {

    public static function getModelConfig($model) {
        $data = json_decode(file_get_contents(config_path('admin/'.$model.'.json')),true);
        return $data;
    }

    public static function getModelFields($model) {
        $config = self::getModelConfig($model);
        return  $config["fields"];
    }


    public static function getModelValidationRules($model,$type = 'store') {
        $fields = self::getModelFields($model);
        $rules = [];
        foreach($fields as $id => $field) {
            if(isset($field['type']) && $field['type'] == 'file') {
                $id = $id.'.*';
            }
            if($type == 'update' && isset($field["validation_update"])) {
                $rules[$id] = $field["validation_update"];
            } else {
                if(isset($field["validation"])) {
                    $rules[$id] = $field["validation"];
                }
            }
        }
        return $rules;
    }

    public static function checkPermissions($model_config, $operation, $object = NULL) {
        if($object && $object->client_id && $object->client_id != Config::get('client_id')) {
            if(!Auth::user()->can('admin_clients')) {
                abort(403);
            }
        }
        if(isset($model_config["permissions"]) && isset($model_config["permissions"][$operation])) {
            foreach($model_config["permissions"][$operation] as $perm) {
                if(Auth::guest() || !Auth::user()->can($perm,$object)) {
                    abort(403, 'Access denied');
                }
            }
        }
    }


    public static function addSelectOptions($model_config) {
        $fields = $model_config['fields'];
        $select_models = [];
        foreach($fields as $id => $field) {
            $type = $field['type'] ?? 'text';
            if($type == 'select') {
                if(isset($field['select_data']) && $field['select_data']['type'] == 'model' && (!isset($field["autocomplete"]) || !$field["autocomplete"])) {
                    $class_name = $field["select_data"]["model"];
                    if(($field["select_data"]["selectOptionsFunction"] ?? null)) {
                        $data = call_user_func($field["select_data"]["selectOptionsFunction"]);
                    } else {
                        $data = $class_name::all();
                    }
                    $select_models[$id] = $data;
                }
            }
        }
        return $select_models;
    }


    public static function getMediaTeasers($config, $obj) {
//        if(get_class($obj) == 'App\Models\Specific\OrganizerShow') {
//            $obj = Show::find($obj->id);
//        }
        if(in_array('App\\Traits\\AnimatedMedia',class_uses(get_class($obj)))) {
            $collections = ['default'];
            foreach($config['fields'] as $f) {
                if($f['type'] == 'file'
                    && isset($f['subtype'])
                    && $f['subtype'] == 'image'
                    && isset($f['collection'])) {
                    $collections[] = $f['collection'];
                }
            }
            $collections = array_unique($collections);
            $arr = [];
            foreach($collections as $c) {
                $media = $obj->getMedia($c);
                foreach($media as $m) {
                    try {
                        $m->url = env('APP_URL').$m->getUrl('thumb');
                    } catch(\Exception $e) {
                        $m->url = env('APP_URL').$m->getUrl();
                    }
                    $m->status = 'finished';
                    $arr[] = $m;
                }
            }
            return $arr;
        }
        return [];
    }


    public static function getClassNameFromModel($model) {
        return "App\\Models\\".Str::studly($model);
    }

}
