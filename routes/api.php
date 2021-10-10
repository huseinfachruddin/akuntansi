<?php

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Akun;
use App\Models\Product;
use App\Models\Substocktransaction;


use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Auth\Auth;
use App\Http\Controllers\User\RoleController;

use App\Http\Controllers\CashController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContacttypeController;
use App\Http\Controllers\ProducttypeController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockNonMoneyController;

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

Route::get('/setup/awal',function(Request $request){
    $role = new Role;
    $role->name='admin';
    $role->save();


    $user = User::create([
        'name' => 'admin awal',
        'email' => 'admin@admin.com',
        'password' => bcrypt('password123'),
    ]);
    $user->syncRoles($role->name);

    return 'Ok';
    });
Route::get('/clean',function(Request $request){
    $akun = Product::whereNotNull('name')->update(array('qty' => 0));
    $akun = Akun::whereNotNull('name')->update(array('total' => 0));
    
    return $akun;
});

Route::get('/test',function(Request $request){
    $subin = Substocktransaction::whereNotNull('purchase_price')->where('product_id','=',$value->id)->get();

    return $subin;
});

Route::get('/report/{name}',[AkunController::class,'reportName']);
Route::get('/report',[AkunController::class,'Report']);

Route::get('/akun',[AkunController::class,'getAkun']);
Route::get('/akun/list/{name}',[AkunController::class,'getAkunHead']);

Route::get('/akun/list',[AkunController::class,'getAkunList']);
Route::get('/akun/iscashout',[AkunController::class,'getAkunIsCashOut']);
Route::get('/akun/iscashin',[AkunController::class,'getAkunIsCashIn']);

Route::get('/akun/iscash',[AkunController::class,'getAkunIsCash']);
Route::get('/akun/notcash',[AkunController::class,'getAkunNotCash']);
Route::get('/akun/isheader',[AkunController::class,'getAkunIsHeader']);
Route::post('/akun/create',[AkunController::class,'createAkun']);
Route::put('/akun/edit/{id}',[AkunController::class,'editAkun']);
Route::delete('/akun/delete/{id}',[AkunController::class,'deleteAkun']);
Route::post('/akun/setlaba',[AkunController::class,'setLabaTahun']);

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
Route::get('/contact/customer',[ContactController::class,'getContactCustomer']);
Route::get('/contact/supplier',[ContactController::class,'getContactSupplier']);
Route::get('/contact/detail/{id}',[ContactController::class,'getContactDetail']);

Route::post('/contact/create',[ContactController::class,'createContact']);
Route::put('/contact/edit/{id}',[ContactController::class,'editContact']);
Route::delete('/contact/delete/{id}',[ContactController::class,'deleteContact']);

// CONTACTTYPE API*********
Route::get('/contacttype',[ContacttypeController::class,'getContacttype']);
Route::get('/contacttype/detail/{id}',[ContacttypeController::class,'getContacttypeDetail']);

Route::post('/contacttype/create',[ContacttypeController::class,'createContacttype']);
Route::put('/contacttype/edit/{id}',[ContacttypeController::class,'editContacttype']);
Route::delete('/contacttype/delete/{id}',[ContacttypeController::class,'deleteContacttype']);

// STOCK ******
Route::get('/stock/transaction',[StockController::class,'getStockTransaction']);
Route::get('/stock/transaction/detail/{id}',[StockController::class,'getStockTransactionDetail']);
Route::get('/stock/in',[StockController::class,'getStockIn']);
Route::get('/stock/out',[StockController::class,'getStockOut']);

Route::post('/stock/in/create',[StockController::class,'createStockIn']);
Route::post('/stock/out/create',[StockController::class,'createStockOut']);

Route::put('/stock/edit/{id}',[StockController::class,'editStockTransaction']);

Route::delete('/stock/transaction/delete/{id}',[StockController::class,'deleteStockTransaction']);

// STOCK Non Money******

Route::get('/stocknonmoney/transaction/detail/{id}',[StockNonMoneyController::class,'getStockTransactionDetail']);
Route::get('/stocknonmoney/in',[StockNonMoneyController::class,'getStockIn']);
Route::get('/stocknonmoney/out',[StockNonMoneyController::class,'getStockOut']);

Route::post('/stocknonmoney/in/create',[StockNonMoneyController::class,'createStockIn']);
Route::post('/stocknonmoney/out/create',[StockNonMoneyController::class,'createStockOut']);

Route::delete('/stocknonmoney/transaction/delete/{id}',[StockNonMoneyController::class,'deleteStockTransaction']);


// #ROLES****
Route::get('/role',[RoleController::class,'getRole']);
Route::post('/role/create',[RoleController::class,'createRole']);
Route::put('/role/edit/{id}',[RoleController::class,'editRole']);
Route::delete('/role/delete/{id}',[RoleController::class,'deleteRole']);

Route::get('/user',[UserController::class,'getUser']);
Route::post('/user/role/create/{id}',[UserController::class,'createUserRole']);
Route::delete('/user/role/delete/{id}',[UserController::class,'deleteUserRole']);


Route::post('/register',[Auth::class,'register']);
Route::post('/login',[Auth::class,'login']);

Route::put('/user/edit/{id}',[UserController::class,'editUser']);
Route::put('/edit/password/{id}',[Auth::class,'editPasswordUser']);

Route::group(['middleware'=>'auth:sanctum'],function(){
    
    Route::get('/logout',[Auth::class,'logout']);
    Route::get('/profile',[UserController::class,'Profile']);
    
    
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





