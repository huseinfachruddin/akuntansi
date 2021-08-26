<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stocktransaction;
use App\Models\Substocktransaction;
use App\Models\Product;
use App\Models\Akun;
use App\Models\Credit;


class StockController extends Controller
{
    public function getStockTransaction(){
        $data = Stocktransaction::with('contact','cashin','cashout')->get();

        $response = [
            'success'=>true,
            'stocktransaction'=>$data,
        ];

        return response($response,200);
    }

    public function getStockIn(){
        $data = Stocktransaction::whereNotNull('cashout_id')->with('contact','cashout')->get();
        
        $response = [
            'success'=>true,
            'stocktransaction'=>$data,

        ];

        return response($response,200);
    }

    public function getStockOut(){
        $data = Stocktransaction::whereNotNull('cashin_id')->with('contact','cashin','credit')->get();
        
        $response = [
            'success'=>true,
            'stocktransaction'=>$data,

        ];

        return response($response,200);
    }

    public function getStockTransactionDetail(Request $request){
        $data = Stocktransaction::where('id',$request->id)->with('contact','cashin','cashout','substocktransaction','substocktransaction.product','credit','credit.cashin')->get();
        
        $response = [
            'success'=>true,
            'stocktransaction'=>$data,

        ];
        
        return response($response,200);
    }

    public function createStockIn(Request $request){

        $request->validate([
            'contact_id' =>'required',
            'cashout_id' =>'required',
            'staff' =>'required',

            'product_id.*' =>'required',
            'qty.*'  =>'required',
            'purchase_price.*'  =>'required',
            'total.*'  =>'required|numeric',
        ]); 

        $stock = new Stocktransaction;
        $stock->contact_id = $request->contact_id;
        $stock->cashout_id = $request->cashout_id;
        $stock->staff = $request->staff;
        
        $stock->save();

        $data = $request->product_id;
        $total = 0;
        foreach ( $data as $key => $value) {
            $sub = new Substocktransaction;
            $sub->stocktransaction_id = $stock->id;
            $sub->product_id = $request->product_id[$key];
            $sub->qty = $request->qty[$key];
            $sub->purchase_price = $request->purchase_price[$key];
            $sub->left = $request->qty[$key];
            $sub->total = $request->total[$key];
            $sub->save();

            $substocktransaction[]= $sub;

            $product = Product::find($sub->product_id);
            $product->qty = $product->qty + $sub->qty;
            $product->save();

            $total = $total + $request->total[$key];

        }      

        $akun = Akun::find($request->cashout_id);
        $akun->total = $akun->total - $total;
        $akun->save();

        $akun = Akun::where('name','=','Persediaan Barang')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total + $total;
        $akun->save();

        $stock = Stocktransaction::find($stock->id);
        $stock->total = $total;
        $stock->save();
        $response = [
            'success'=>true,
            'stockktransaction'=>$stock,
            'substocktransaction'=>$substocktransaction,
        ];

        return response($response,200);
    }

    public function createStockOut(Request $request){

        $request->validate([
            'contact_id' =>'required',
            'cashin_id' =>'required',
            'staff' =>'required',
            'paid' =>'required',
            'payment_due' =>'required',

            'product_id.*' =>'required',
            'qty.*'  =>'required',
            'total.*'  =>'required|numeric',
        ]); 

        $stock = new Stocktransaction;
        $stock->contact_id = $request->contact_id;
        $stock->cashin_id = $request->cashin_id;
        $stock->staff = $request->staff;
        $stock->paid = $request->paid;
        $stock->payment_due = $request->payment_due;

        $stock->save();

        $data = $request->product_id;
        $total = 0;
        $totalhpp=0;
        foreach ( $data as $key => $value) {
            $sub = new Substocktransaction;
            $sub->stocktransaction_id = $stock->id;
            $sub->product_id = $request->product_id[$key];
            $sub->qty = $request->qty[$key];
            $sub->total = $request->total[$key];
            $sub->save();

            $substocktransaction[]= $sub;

            $product = Product::find($sub->product_id);
            $product->qty = $product->qty - $sub->qty;
            $product->save();
            
            $qty = $sub->qty;
            $subin = Substocktransaction::where('left','>',0)->where('product_id','=',$sub->product_id)->get();
            foreach ($subin as $key => $value) {

                if ($qty <= $value->left) {

                    $set = $value->left-$qty;
                    
                    $sibin = Substocktransaction::find($value->id);
                    $sibin->left = $set;
                    $sibin->save();

                    $totalhpp = $totalhpp +($value->purchase_price * $qty);
                    break;
                }else{
                    $set = 0;
                    $qty = $qty - $value->left;
                    $totalhpp = $totalhpp + ($value->purchase_price * $value->left);

                    $sibin = Substocktransaction::find($value->id);
                    $sibin->left = $set;
                    $sibin->save();

                }
                
            }
            $sibin = Substocktransaction::find($sub->id);
            $sibin->hpp = $totalhpp;
            $sibin->save();

            $total = $total + $sub->total;
            
        }      

        $akun = Akun::where('name','=','Persediaan Barang')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total - $totalhpp;
        $akun->save();

        $akun = Akun::where('name','=','Pendapatan Penjualan')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total + $total;
        $akun->save();

        $akun = Akun::where('name','=','Harga Pokok Penjualan')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total + $totalhpp;
        $akun->save();

        $stock = Stocktransaction::find($stock->id);
        $stock->total = $total;
        $stock->save();
        
        $akun = Akun::find($request->cashin_id);
        $akun->total = $akun->total + $stock->paid;
        $akun->save();
        
        $akun = Akun::where('name','=','Piutang Penjualan')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total + ($total-$stock->paid);
        $akun->save();

        $credit = new Credit;
        $credit->stocktransaction_id = $stock->id;
        $credit->cashin_id = $stock->cashin_id;
        $credit->total = $stock->paid;
        $credit->save();

        $response = [
            'success'=>true,
            'stockktransaction'=>$stock,
            'substocktransaction'=>$substocktransaction,
        ];
        return response($response,200);
    }

    public function editStockTransaction(Request $request){
        $request->validate([
            'cashin_id' =>'required',
            'total' =>'required',
            'payment_due' =>'required',

        ]);
        
        $stock = Stocktransaction::find($request->id);
        $stock->paid = $stock->paid + $request->total;
        $stock->payment_due = $request->payment_due;
        $stock->save();

        $akun = Akun::find($request->cashin_id);
        $akun->total = $akun->total + $request->total;
        $akun->save();
        
        $akun = Akun::where('name','=','Piutang Penjualan')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total - $request->total;
        $akun->save();

        $credit = new Credit;
        $credit->stocktransaction_id = $stock->id;
        $credit->cashin_id = $request->cashin_id;
        $credit->total = $request->total;
        $credit->payment_due = $request->payment_due;
        $credit->save();

        $response = [
            'stockktransaction'=>$stock,
        ];

        return response($response,200);
    }

    public function deleteStockTransaction(Request $request){

        $stock = Stocktransaction::find($request->id);
        if ($stock->cashin_id) {

            $akun = Akun::find($stock->cashin_id);
            $akun->total = $akun->total - $stock->paid;
            $akun->save();

            $akun = Akun::where('name','=','Piutang Penjualan')->first();
            $akun = Akun::find($akun->id);
            $akun->total = $akun->total - ($stock->paid - $stock->paid);
            $akun->save();

            $sub =Substocktransaction::where('stocktransaction_id','=',$stock->id)->get();
            $totalhpp=0;
            foreach ($sub as $key => $value) {
                $product = Product::find($value->product_id);
                $product->qty = $product->qty + $value->qty;
                $product->save(); 

                $totalhpp = $totalhpp + $value->hpp;
                $qty = $value->qty;
                $subin = Substocktransaction::whereNotNull('purchase_price')->where('product_id','=',$value->id)->orderBy('id','desc')->get();
                foreach ($subin as $key => $value) {
                    
                    if ($qty <= $value->qty) {
    
                        $set = $value->left + $qty;
                        
                        $sibin = Substocktransaction::find($value->id);
                        $sibin->left = $set;
                        $sibin->save();
                        break;
                    }else{
                        $set = $value->qty;
                        $qty = $qty - $value->qty;
    
                        $sibin = Substocktransaction::find($value->id);
                        $sibin->left = $set;
                        $sibin->save();
    
                    }
                    
                }

            }

            $akun = Akun::where('name','=','Persediaan Barang')->first();
            $akun = Akun::find($akun->id);
            $akun->total = $akun->total + $totalhpp;
            $akun->save();

           $akun = Akun::where('name','=','Pendapatan Penjualan')->first();
           $akun = Akun::find($akun->id);
           $akun->total = $akun->total - $stock->total;
           $akun->save();

           $akun = Akun::where('name','=','Harga Pokok Penjualan')->first();
           $akun = Akun::find($akun->id);
           $akun->total = $akun->total - $totalhpp;
           $akun->save();

           Substocktransaction::where('stocktransaction_id','=',$stock->id)->delete();
           Credit::where('stocktransaction_id','=',$stock->id)->delete();


        }elseif ($stock->cashout_id) {
            $akun = Akun::find($stock->cashout_id);
            $akun->total = $akun->total + $stock->total;
            $akun->save();

            $totalhpp=0;
            $sub =Substocktransaction::where('stocktransaction_id','=',$stock->id)->get();
            foreach ($sub as $key => $value) {
                $product = Product::find($value->product_id);
                $product->qty = $product->qty - $value->qty;
                $product->save(); 

                $totalhpp = $totalhpp + ($value->left * $value->purchase_price);
            }

            $akun = Akun::where('name','=','Persediaan Barang')->first();
            $akun = Akun::find($akun->id);
            $akun->total = $akun->total - $stock->total;
            $akun->save();

           Substocktransaction::where('stocktransaction_id','=',$stock->id)->delete();
        }
        $stock->delete();
        
        $response = [
            'success'=>true,
            'stocktransaction'=>$stock,
        ];

        return response($response,200);
    }
}
