<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\General\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function getUser(Request $request, $id) {
        return json_encode(User::find($id));
    }

}
