<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    public function createUserRole(){
        $request->validate([
            'user_id' =>'required',
            'role' =>'required',
        ]);

        $user = User::find($request->id);
        $role = $user->assignRole($request->name);

        $response = [
            'success'=>true,
            'user'  =>$user ,
            'role'  =>$role,
        ];
        return response($response,200);

    }

    public function getUser(){

        $user = User::all();

        $response = [
            'success'=>true,
            'user'  =>$user ,
        ];

        return response($response,200);

    }
}
