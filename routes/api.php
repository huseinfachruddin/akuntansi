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
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ProducttypeController;
use App\Http\Controllers\StockController;

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

Route::get('/test',[AkunController::class,'test']);

Route::get('/report',[AkunController::class,'Report']);
Route::get('/akun',[AkunController::class,'getAkun']);
Route::get('/akun/list',[AkunController::class,'getAkunList']);
Route::get('/akun/iscash',[AkunController::class,'getAkunIsCash']);
Route::get('/akun/notcash',[AkunController::class,'getAkunNotCash']);
Route::get('/akun/isheader',[AkunController::class,'getAkunIsHeader']);
Route::post('/akun/create',[AkunController::class,'createAkun']);
Route::put('/akun/edit/{id}',[AkunController::class,'editAkun']);
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

// PRODUCT API*********
Route::get('/product',[ProductController::class,'getProduct']);
Route::get('/product/detail/{id}',[ProductController::class,'getProductDetail']);

Route::post('/product/create',[ProductController::class,'createProduct']);
Route::put('/product/edit/{id}',[ProductController::class,'editProduct']);
Route::delete('/product/delete/{id}',[ProductController::class,'deleteProduct']);


// PRODUCT TYPE API*********
Route::get('/producttype',[ProducttypeController::class,'getProducttype']);
Route::get('/producttype/detail/{id}',[ProducttypeController::class,'getProducttypeDetail']);

Route::post('/producttype/create',[ProducttypeController::class,'createProducttype']);
Route::put('/producttype/edit/{id}',[ProducttypeController::class,'editProducttype']);
Route::delete('/producttype/delete/{id}',[ProducttypeController::class,'deleteProducttype']);

// CONTACT API*********
Route::get('/contact',[ContactController::class,'getContact']);
Route::get('/contact/detail/{id}',[ContactController::class,'getContactDetail']);

Route::post('/contact/create',[ContactController::class,'createContact']);
Route::put('/contact/edit/{id}',[ContactController::class,'editContact']);
Route::delete('/contact/delete/{id}',[ContactController::class,'deleteContact']);



// STOCK ******

Route::get('/stock/transaction',[StockController::class,'getStockTransaction']);
Route::get('/stock/transaction/detail/{id}',[StockController::class,'getStockTransactionDetail']);

Route::post('/stock/in/create',[StockController::class,'createStockIn']);
Route::post('/stock/out/create',[StockController::class,'createStockOut']);


Route::delete('/stock/transaction/delete/{id}',[StockController::class,'deleteStockTransaction']);





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



