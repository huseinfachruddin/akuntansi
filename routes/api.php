<?php

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Auth\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/test', function(){
    // $role=Role::where('name','super-admin')->delete();
    // if(Role::where('name','super-admin')){
    //     $role = Role::create(['name' => 'super-admin']);
    //     dd('masuk');
    // }

    $user = User::find(1)->assignRole('super-admin');

});

Route::post('/register',[Auth::class,'register']);
Route::post('/login',[Auth::class,'login']);
Route::get('/logout',[Auth::class,'logout']);

Route::group(['middleware'=>'auth:sanctum'],function(){ 
    Route::get('/users',function(Request $request){
        return $request->user();
    });
    Route::group(['middleware' => ['role:super-admin']], function () {
        Route::get('/users/super',function(Request $request){
            return 'Ok';
        });
    });
    Route::group(['middleware' => ['role:admin']], function () {
        Route::get('/users/admin',function(Request $request){
            return $request->user();
        });
    });
});

