<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
//use App\Models\Instruction;
use App\Models\General\PageBlock;
use App\Models\General\Setting;
//use App\Models\SiteMapHelper;
//use App\Models\Tag;
use App\Models\Specific\Show;
use App\Models\Specific\Staff;
use App\Models\Specific\VenueScheme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

class AdminController extends Controller
{
    public function index() {
        return view('layouts.admin.admin');
    }

    public function getPermissions() {
        $data = json_decode(file_get_contents(config_path('admin/general_config.json')),true);
        $permissions = $data['permissions'];
        foreach($permissions as $model => $actions) {
            foreach($actions as $action) {
                Permission::updateOrCreate(['name' => $model.'_'.$action]);
            }
        }
        return response()->json(Permission::all());
    }

    public function getMyPermissions() {
        return response()->json(Auth::user()->getAllPermissions()->pluck('name')->toArray());
    }


    public function getInstruction(Request $request) {
        $inst =  Instruction::where('link',$request->link)->first();
        return json_encode($inst);
    }


    public function getTags(Request $request) {
        if($request->type) {
            return json_encode(Tag::where('type',$request->type)->pluck('title')->toArray());
        }
        return json_encode(Tag::pluck('title')->toArray());
    }

    public function getPageBlocks($page_id) {
        $blocks = PageBlock::where('page_id',$page_id)->orderBy('sort_order','asc')->get();
        return response()->json($blocks);
    }

    public function clearCache() {
        Cache::flush();
    }

//    public function sitemap() {
//        SiteMapHelper::generateSitemap();
//    }

    public function env() {
        return response()->json([
            'LANGS' => env('LANGS'),
        ]);
    }

    public function quillSaveImage(Request $request) {
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('/images/quill', ['disk' => 'public']);
//            return response()->json(str_replace('public/','/storage/', $path));
            return response()->json('/'.$path);
        }
        return false;
    }
//
//    public function saveEnv(Request $request) {
//        $txt = file_get_contents(base_path('.env'));
//        $data = $request->all();
//        foreach($data as $k => $v) {
//            $newval = $v == 1 ? 1 : 0;
//            $txt = str_replace($k.'='.env($k),$k.'='.$newval,$txt);
//        }
//        file_put_contents(base_path('.env'),$txt);
//    }

    public function getShowRoles($show_id) {
        if($show_id != 'new') {
            $show = Show::find($show_id);
            $data = $show->roles()->with('actors')->get();
        } else {
            $data = [];
        }
        return response()->json([
            'roles' => $data,
            'actors' => Staff::active()->where('actor',1)->sorted()->get()
        ]);
    }

    public function getShowAfisha($show_id) {
        $show = Show::find($show_id);
        $venue = $show->venue;
        $schemas = $venue ? $venue->schemes : VenueScheme::all();
        return response()->json([
            'timetables' => $show->timetables,
            'schemas'    => $schemas
        ]);
    }

    public function getResume($filename) {
        return response()->download(storage_path('app/resumes/' . $filename));
    }

}
