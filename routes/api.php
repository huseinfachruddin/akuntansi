<?php

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Akun;
use App\Models\Contact;
use App\Models\Credit;
use App\Models\Product;
use App\Models\Substocktransaction;
use App\Models\Stocktransaction;
use App\Models\Cashtransaction;
use App\Models\Subcashtransaction;

use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Auth\Auth;
use App\Http\Controllers\User\RoleController;

use App\Http\Controllers\CashController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PriceproductController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContacttypeController;
use App\Http\Controllers\ProducttypeController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockDebtController;
use App\Http\Controllers\StockNonMoneyController;
use App\Http\Controllers\StockorderController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportNeracaController;

use Carbon\Carbon;
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
    $akun = Product::whereNotNull('name')->where('category','<>','service')->update(array('qty' => 0));
    $akun = Stocktransaction::whereNotNull('id')->delete();
    $akun = Substocktransaction::whereNotNull('id')->delete();
    $akun = Cashtransaction::whereNotNull('id')->delete();
    $akun = Subcashtransaction::whereNotNull('id')->delete();
    $akun = Credit::whereNotNull('id')->delete();
    $akun = Akun::whereNotNull('name')->update(array('total' => 0));
    
    return $akun;
});

Route::get('/test',function(Request $request){
    // $stock = Stocktransaction::first()->date;
    // $data = Carbon::create($stock)->diffForHumans();
    $data =Akun::where('iscashin',true)->first()->id;
    return $data;
});

Route::match(['get','post'],'/report/{name}', [ReportController::class,'AkunReportLaba']);
Route::match(['get','post'],'/report/akun/neraca', [ReportNeracaController::class,'AkunReportNeraca']);
Route::match(['get','post'],'/report/neraca/{name}', [ReportNeracaController::class,'AkunReportNeraca']);

// Route::get('/report/{name}',[AkunController::class,'reportName']);
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
// Cash API++++

Route::match(['get','post'], '/cash', [CashController::class,'getCash']);
Route::match(['get','post'], '/cash/in', [CashController::class,'getCashIn']);
Route::match(['get','post'], '/cash/out', [CashController::class,'getCashOut']);
Route::match(['get','post'], '/cash/transfer', [CashController::class,'getCashTransfer']);

Route::get('/cash/transaction/detail/{id}',[CashController::class,'getCashTransactionDetail']);

Route::post('/cash/in/create',[CashController::class,'createCashIn']);
Route::post('/cash/out/create',[CashController::class,'createCashOut']);
Route::post('/cash/transfer/create',[CashController::class,'createCashTransfer']);

Route::delete('/cash/transaction/delete/{id}',[CashController::class,'deleteCashTransaction']);

// UNIT API *********
Route::match(['get','post'], '/unit', [UnitController::class,'getUnit']);

Route::post('/unit/create',[UnitController::class,'createUnit']);
Route::put('/unit/edit/{id}',[UnitController::class,'editUnit']);
Route::delete('/unit/delete/{id}',[UnitController::class,'deleteUnit']);

// PRODUCT API *********
Route::match(['get','post'], '/product/all', [ProductController::class,'getProductAll']);
Route::match(['get','post'], '/product', [ProductController::class,'getProduct']);
Route::match(['get','post'], '/product/goods', [ProductController::class,'getProductGoods']);
Route::match(['get','post'], '/product/service', [ProductController::class,'getProductService']);

Route::get('/product/detail/{id}',[ProductController::class,'getProductDetail']);

Route::post('/product/create',[ProductController::class,'createProduct']);
Route::put('/product/edit/{id}',[ProductController::class,'editProduct']);
Route::delete('/product/delete/{id}',[ProductController::class,'deleteProduct']);

// PRODUCT TYPE API*********
Route::get('/producttype',[ProducttypeController::class,'getProducttype']);
Route::get('/producttype/detail/{id}',[ProducttypeController::class,'getProducttypeDetail']);
Route::post('/producttype/detail/{id}',[ProducttypeController::class,'getProducttypeDetail']);

Route::post('/producttype/create',[ProducttypeController::class,'createProducttype']);
Route::put('/producttype/edit/{id}',[ProducttypeController::class,'editProducttype']);
Route::delete('/producttype/delete/{id}',[ProducttypeController::class,'deleteProducttype']);

// Price Product.........
Route::get('/product/price/detail/{id}',[PriceproductController::class,'detailProductPrice']);
Route::post('/product/price/create',[PriceproductController::class,'cratePrice']);
Route::delete('/product/price/delete/{id}',[PriceproductController::class,'deletePrice']);
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
// STOCK REPORT ******
Route::match(['get','post'], '/stock/out/report', [StockController::class,'getStockOutReport']);

// STOCK ******
Route::get('/stock/transaction',[StockController::class,'getStockTransaction']);
Route::get('/stock/transaction/detail/{id}',[StockController::class,'getStockTransactionDetail']);

Route::match(['get','post'], '/stock/in', [StockController::class,'getStockIn']);
Route::match(['get','post'], '/stock/out', [StockController::class,'getStockOut']);


Route::post('/stock/in/create',[StockController::class,'createStockIn']);
Route::post('/stock/out/create',[StockController::class,'createStockOut']);

Route::delete('/stock/transaction/delete/{id}',[StockController::class,'deleteStockTransaction']);

// STOCK DEBT******

Route::match(['get','post'], '/stock/out/debt', [StockDebtController::class,'getStockOutDebt']);
Route::match(['get','post'], '/stock/out/debt/due', [StockDebtController::class,'getStockOutDebtDue']);

Route::put('/stock/out/paid/{id}',[StockDebtController::class,'paidStockOut']);
Route::put('/stock/in/paid/{id}',[StockDebtController::class,'paidStockIn']);

Route::delete('/stock/paid/delete/{id}',[StockDebtController::class,'deleteCreditTransaction']);
// STOCK Non Money******

Route::get('/stock/nonmony/detail/{id}',[StockNonMoneyController::class,'getStockTransactionDetail']);


Route::match(['get','post'], '/stock/in/nonmoney', [StockNonMoneyController::class,'getStockIn']);
Route::match(['get','post'], '/stock/out/nonmoney', [StockNonMoneyController::class,'getStockOut']);


Route::post('/stock/in/nonmoney/create',[StockNonMoneyController::class,'createStockIn']);
Route::post('/stock/out/nonmoney/create',[StockNonMoneyController::class,'createStockOut']);

Route::delete('/stock/nonmoney/delete/{id}',[StockNonMoneyController::class,'deleteStockTransaction']);

// STOCK Order leter******
Route::match(['get','post'], '/stock/pending/out/due', [StockorderController::class,'getStockOutDue']);
Route::get('/stock/pending/detail/{id}',[StockorderController::class,'getStockTransactionDetail']);

Route::match(['get','post'], '/stock/pending/in', [StockorderController::class,'getStockIn']);
Route::match(['get','post'], '/stock/pending/out', [StockorderController::class,'getStockOut']);

Route::post('/stock/in/pending/create',[StockorderController::class,'createStockIn']);
Route::post('/stock/out/pending/create',[StockorderController::class,'createStockOut']);

Route::delete('/stock/pending/delete/{id}',[StockorderController::class,'deleteStockTransaction']);

// #ROLES****
Route::get('/role',[RoleController::class,'getRole']);
Route::post('/role/create',[RoleController::class,'createRole']);
Route::put('/role/edit/{id}',[RoleController::class,'editRole']);
Route::delete('/role/delete/{id}',[RoleController::class,'deleteRole']);

Route::get('/user',[UserController::class,'getUser']);
Route::post('/user/role/create/{id}',[UserController::class,'createUserRole']);
Route::delete('/user/role/delete/{id}',[UserController::class,'deleteUserRole']);
Route::delete('/user/delete/{id}',[UserController::class,'deleteUser']);


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





