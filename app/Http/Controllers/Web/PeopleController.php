<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Specific\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PeopleController extends Controller {
    
    public function people(Request $request, $group = null) {
        if(!$group) return redirect('/'.app()->getLocale().'/people/actor');
        $letters = Staff::lettersForPeople();
        $q = Staff::sorted()->active();
        if($group) {
            $q->where($group, 1);
        }  
        $people = $q->paginate(12);
        $submenu = $this->groupsToSubmenu(Staff::getGroups());
        return view('content.people.people', compact('people', 'letters', 'submenu', 'group'));        
    }
    
    public function person(Request $request, $slug) {
        $person = Staff::findBySlug($slug);
        if(!$person || !$person->active) abort(404);
        $gallery = $person->media()->where('collection_name','wallpaper')->get();
        $roles = $person->getRoles();
        return view('content.people.person', compact('person', 'gallery', 'roles'));
    }
    
    public function letter($group, $letter) {
        $people = Staff::active()
            ->where($group, 1)
            ->where('name->'.app()->getLocale(), 'LIKE', $letter.'%')
            ->get();
        $html = view('content.people.people-dynamic', compact('people'))->render();
        return response()->json($html);
    }
    
    public function letters($group) {
        $letters = Staff::lettersForPeople($group);
        return response()->json($letters);
    }
    
    private function groupsToSubmenu($groups) {
        $menu = [];
        foreach($groups as $gId => $gTitle) {
            $menu[] = [
                'url' => 'people/'.$gId,
                'title' => __($gTitle)
            ];
        }
        return $menu;
    }

   
}
