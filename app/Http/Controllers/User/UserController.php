<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;


class UserController extends Controller
{
    public function createUserRole(Request $request){
        $request->validate([
            'role' =>'required',
        ]);

        $user = User::find($request->id)->first();
        $user = $user->assignRole($request->role);

        $response = [
            'success'=>true,
            'user'  =>$user,
        ];
        return response($response,200);

    }

    public function deleteUserRole(Request $request){
        $request->validate([
            'role' =>'required',
        ]);

        $user = User::find($request->id);
        $user = $user->removeRole($request->role);

        $response = [
            'success'=>true,
            'user'  =>$user,
        ];
        return response($response,200);

    }

    public function getUser(){

        $user = User::with('roles')->get();

        $response = [
            'success'=>true,
            'user'  =>$user ,
        ];

        return response($response,200);

    }

    public function Profile(Request $request){

        $user = User::where('id',$request->user()->id)->with('roles')->get();
        // $user = $request->user()->id;

        $response = [
            'success'=>true,
            'user'  =>$user ,
        ];

        return response($response,200);

    }
}
