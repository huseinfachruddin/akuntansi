<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stocktransaction;
use App\Models\Substocktransaction;
use App\Models\Product;
use App\Models\Akun;

class StockController extends Controller
{
    private function saveAkun(){
        $data=Product::total();
        if ($data->total) {
            $data=Akun::where('name','=','Persediaan Barang')->update(array('total' => $data->total));
        }else {
            $data=Akun::where('name','=','Persediaan Barang')->update(array('total' => 0));
        }

        $response = [
            'success'=>true,
            'product'=>$data,
        ];
        
        return response($response,200);
    }
    public function getStockTransaction(){
        $data = Stocktransaction::with('contact','cashin','cashout')->get();
        $this->saveAkun();

        $response = [
            'success'=>true,
            'stocktransaction'=>$data,
        ];
        $this->saveAkun();

        return response($response,200);
    }

    public function getStockIn(){
        $data = Stocktransaction::whereNotNull('cashout_id')->with('contact','cashout')->get();
        
        $response = [
            'success'=>true,
            'stocktransaction'=>$data,

        ];
        $this->saveAkun();

        return response($response,200);
    }

    public function getStockOut(){
        $data = Stocktransaction::whereNotNull('cashin_id')->with('contact','cashin')->get();
        
        $response = [
            'success'=>true,
            'stocktransaction'=>$data,

        ];
        $this->saveAkun();

        return response($response,200);
    }

    public function getStockTransactionDetail(Request $request){
        $data = Stocktransaction::where('id',$request->id)->with('contact','cashin','cashout','substocktransaction','substocktransaction.product')->get();
        
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

            'product_id.*' =>'required',
            'qty.*'  =>'required',
            'total.*'  =>'required|numeric',
        ]); 

        $stock = new Stocktransaction;
        $stock->contact_id = $request->contact_id;
        $stock->cashout_id = $request->cashout_id;
        $stock->save();

        $data = $request->product_id;
        $total = 0;
        foreach ( $data as $key => $value) {
            $sub = new Substocktransaction;
            $sub->stocktransaction_id = $stock->id;
            $sub->product_id = $request->product_id[$key];
            $sub->qty = $request->qty[$key];
            $sub->total = $request->total[$key];
            $sub->save();

            $substocktransaction[]= $sub;

            $product = Product::find($request->product_id)->first();
            $product->qty = $product->qty + $sub->qty;
            $product->save();

            $total = $total + $request->total[$key];

        }      

        $akun = Akun::find($request->cashout_id);
        $akun->total = $akun->total - $total;
        $akun->save();

        $stock = Stocktransaction::find($stock->id);
        $stock->total = $total;
        $stock->save();
        $this->saveAkun();
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

            'product_id.*' =>'required',
            'qty.*'  =>'required',
            'total.*'  =>'required|numeric',
        ]); 

        $stock = new Stocktransaction;
        $stock->contact_id = $request->contact_id;
        $stock->cashin_id = $request->cashin_id;
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

            $product = Product::find($request->product_id)->first();
            $product->qty = $product->qty - $sub->qty;
            $product->save();

            $totalhpp = $totalhpp + ($product->purchase_price * $sub->qty);
            $total = $total + $request->total[$key];
        }      

        $akun = Akun::where('name','Pendapatan Penjualan')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total + $total;
        $akun->save();

        $akun = Akun::where('name','Harga Pokok Penjualan')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total + $totalhpp;
        $akun->save();

        $akun = Akun::find($request->cashin_id);
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
        $this->saveAkun();
        return response($response,200);
    }

    public function deleteStockTransaction(Request $request){

        $stock = Stocktransaction::find($request->id);
        if ($stock->cashin_id) {

            $akun = Akun::find($stock->cashin_id);
            $akun->total = $akun->total - $stock->total;
            $akun->save();

            Subcashtransaction::where('stocktransaction_id',$request->id)->get();
            $totalhpp=0;
            foreach ($sub as $key => $value) {
                $product = Product::find($value->product_id)->first();
                $product->qty = $product->qty + $value->qty;
                $product->save(); 

                $totalhpp = $totalhpp + ($product->purchase_price * $value->qty);

            }
           $akun = Akun::where('name','Pendapatan Penjualan')->first();
           $akun = Akun::find($akun->id);
           $akun->total = $akun->total - $stock->total;
           $akun->save();

           $akun = Akun::where('name','Harga Pokok Penjualan')->first();
           $akun = Akun::find($akun->id);
           $akun->total = $akun->total - $totalhpp;
           $akun->save();
           $this->saveAkun();

           Substocktransaction::where('stocktransaction_id',$stock->id)->delete();
        }elseif ($stock->cashout_id) {
            $akun = Akun::find($stock->cashout_id);
            $akun->total = $akun->total + $stock->total;
            $akun->save();

            Subcashtransaction::where('stocktransaction_id',$request->id)->get();
            foreach ($sub as $key => $value) {
                $product = Product::find($value->product_id)->first();
                $product->qty = $product->qty - $value->qty;
                $product->save(); 
           }
           $this->saveAkun();

           Substocktransaction::where('stocktransaction_id',$stock->id)->delete();
        }
        $stock->delete();
        
        $this->saveAkun();
        $response = [
            'success'=>true,
            'stocktransaction'=>$stock,
        ];
        
        return response($response,200);
    }
}
