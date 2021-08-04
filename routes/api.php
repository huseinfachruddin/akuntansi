<?php

use Illuminate\Http\Request;
use App\Models\User;


use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Auth\Auth;
use App\Http\Controllers\CashController;
use App\Http\Controllers\AkunController;

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

Route::get('/report',[AkunController::class,'Report']);
Route::get('/akun',[AkunController::class,'getAkun']);
Route::get('/akun/iscash',[AkunController::class,'getAkunIsCash']);
Route::get('/akun/notcash',[AkunController::class,'getAkunNotCash']);
Route::post('/akun/create',[AkunController::class,'createAkun']);
Route::put('/akun/edite/{id}',[AkunController::class,'editAkun']);
Route::delete('/akun/delete/{id}',[AkunController::class,'deleteAkun']);

Route::get('/cash',[CashController::class,'getCash']);
Route::get('/cash/in',[CashController::class,'getCashIn']);
Route::get('/cash/out',[CashController::class,'getCashOut']);
Route::get('/cash/transfer',[CashController::class,'getCashTransfer']);
Route::get('/cash/transaction/detail/{id}',[CashController::class,'getCashTransactionDetail']);

Route::post('/cash/in/create',[CashController::class,'createCashIn']);
Route::post('/cash/out/create',[CashController::class,'createCashOut']);
Route::post('/cash/transfer/create',[CashController::class,'createCashTransfer']);

Route::delete('/cash/transaction/delete/{id}',[CashController::class,'deleteCashTransaction']);



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



