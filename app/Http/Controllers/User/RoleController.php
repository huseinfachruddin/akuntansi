<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function getRole(){
        $data = Role::all();

        $response = [
            'success'=>true,
            'role'  =>$data   ,
        ];
        return response($response,200);

    }

    public function createRole(Request $request){

        $request->validate([
            'name' =>'required',
        ]);

        $data = new Role;
        $data->name = $request->name;
        $data->save();

        $response = [
            'success'=>true,
            'role'  =>$data   ,
        ];

        return response($response,200);

    }

    public function editRole(Request $request){

        $request->validate([
            'name' =>'required',
        ]);

        $data = Role::find($request->id);
        $data->name = $request->name;
        $data->save();

        $response = [
            'success'=>true,
            'role'  =>$data   ,
        ];

        return response($response,200);

    }

    public function deleteRole(Request $request){

        $data = Role::find($request->id);
        $data->delete();

        $response = [
            'success'=>true,
            'role'  =>$data   ,
        ];

        return response($response,200);

    }
}
