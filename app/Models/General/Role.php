<?php

namespace App\Models\General;

use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public static function customCreate($request) {
        $data = $request->all();
        unset($data['permission']);
        $obj = Role::create($data);
        $obj->syncPermissions($request->permission);
        return $obj;
    }


    public function customUpdate($request) {
        $data = $request->all();
        unset($data['permission']);
        $this->update($data);
        $this->syncPermissions($request->permission);
        return $this;
    }

//    public static function initialAdminCreate() {
//        $role = Role::create(['name' => 'admin']);
//        $permission = Permission::create(['name' => 'admin_panel']);
//        $role->givePermissionTo($permission);
//        $user = Auth::user();
//        $user->assignRole('admin');
//    }

    public static function getSelectOptions() {
        $q = Role::query();
        if(clientId()) {
            $q->where('name', '<>', 'superadmin');
        }
        return $q->get();
    }
}
