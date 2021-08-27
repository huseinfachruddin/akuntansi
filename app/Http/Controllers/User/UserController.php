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

        $user = User::where('id',$request->id)->first();
        $user = $user->syncRoles($request->role);
 
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

    public function editUser(Request $request){
        $request->validate([
            'name'  =>'required',
            'email' =>'required|email|unique:users,email',
        ]);

        $user = User::find($request->id);
        $user->name = $request->name;
        $user->email = $request->email;

        $response = [
            'success'=>true,
            'user'  =>$user ,
        ];

        return response($response,200);

    }

    public function editPasswordUser(Request $request){
        $request->validate([
            'password'  =>'required',
            'newPassword' =>'required|email|unique:users,email',
            'rePassword'  =>'required|min:6|same:newPassword',
        ]);

        $user = User::find($request->id);

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'success'   => false,
                'errors' => ['auth'=> 'Incorrect password']
            ], 404);
        }

        $user->password = bcrypt($request->newPassword);

        $response = [
            'success'=>true,
            'user'  =>$user ,
        ];

        return response($response,200);

    }
}
