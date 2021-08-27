<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
// use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;

class Auth extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'  =>'required',
            'email' =>'required|email|unique:users,email',
            'password'  =>'required|min:6',
            'rePassword'  =>'required|min:6|same:password',
        ]);


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $response = [
            'success'=>true,
            'user'  =>$user,
        ];

        return response($response,200);
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user= User::where('email', $request->email)->first();
        
            if (!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'success'   => false,
                    'errors' => ['auth'=> 'Incorrect email and password']
                ], 404);
            }
        
            $token = $user->createToken('ApiToken')->plainTextToken;
        
            $response = [
                'success'   => true,
                'user'      => $user,
                'token'     => $token
            ];
        
        return response($response, 200);
    }

    public function editPasswordUser(Request $request){
        $request->validate([
            'password'  =>'required',
            'newPassword' =>'required|min:6',
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
        $user->save();

        $response = [
            'success'=>true,
            'user'  =>$user ,
        ];

        return response($response,200);
    }
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        $response = [
            'success'=>true,
            'message'=>'Anda berhasil di logout',
        ];
        return response($response,200);
    }
}
