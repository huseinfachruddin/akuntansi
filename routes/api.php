<?php

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cash;
use App\Models\Cashintrans;

use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Auth\Auth;
use App\Http\Controllers\CashController;

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
    // Role::create(['name' => 'admin']);
    // User::find(1)->assignRole('admin');
    $data = 'CIT'.rand().time();
    // // $data = CashController::Cashdetail(1);
    return $data;
return 'ok';

});

// Route::get('/cash/out',[CashController::class,'Cashout']);
// Route::get('/cash/out/total',[CashController::class,'CashOutTotal']);
// Route::get('/cash/{id}/detail',[CashController::class,'CashOutdetail']);
// Route::post('/cash/out/create',[CashController::class,'CashOutCreate']);

// Route::get('/cash',[CashController::class,'Cash']);

// Route::get('/cash/in',[CashController::class,'Cashintrans']);
// Route::get('/cash/in/total',[CashController::class,'CashinTotal']);
// Route::get('/cash/{id}/detail',[CashController::class,'Cashdetail']);
// Route::post('/cash/In/create',[CashController::class,'CashInCreate']);




Route::post('/register',[Auth::class,'register']);
Route::post('/login',[Auth::class,'login']);

Route::group(['middleware'=>'auth:sanctum'],function(){ 
    Route::get('/logout',[Auth::class,'logout']);

    Route::get('/profile',function(Request $request){
        return $request->user();
    });
    Route::group(['middleware' => ['role:admin']], function () {
        Route::get('/admin',function(Request $request){
            return 'Ok';
        });
    });
    Route::group(['middleware' => ['role:admin']], function () {
        Route::get('/users/admin',function(Request $request){
            return $request->user();
        });
    });
});



