<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StockorderController extends Controller
{
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
            'paid' =>'required',
            'payment_due' =>'required',
            'date' =>'required',

            'product_id.*' =>'required',
            'qty.*'  =>'required',
            'purchase_price.*'  =>'required',
            'total.*'  =>'required|numeric',
        ]); 
        
        $stock = new Stocktransaction;
        $stock->contact_id = $request->contact_id;
        $stock->cashout_id = $request->cashout_id;
        $stock->staff = $request->staff;
        $stock->date = date("Y-m-d", strtotime($request->date));
        $stock->paid = $request->paid;
        $stock->payment_due = date("Y-m-d", strtotime($request->payment_due));
        
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

        $credit = new Credit;
        $credit->stocktransaction_id = $stock->id;
        $credit->cashout_id = $stock->cashout_id;
        $credit->total = $stock->paid;
        $credit->save();

        $akun = Akun::where('name','=','Hutang Pembelian Non Tunai')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total + ($total - $stock->paid);
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
}
