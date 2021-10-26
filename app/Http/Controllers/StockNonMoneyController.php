<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stocktransaction;
use App\Models\Substocktransaction;
use App\Models\Product;
use App\Models\Akun;
use App\Models\Credit;
use App\Models\Contact;

class StockNonMoneyController extends Controller
{
    
    public function getStockIn(Request $request){
        
        $data = Stocktransaction::where('nonmoney','in');
        
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
            $data = $data->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $data = $data->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
        }
        $data = $data->get();

        $response = [
            'success'=>true,
            'stocktransaction'=>$data,
        ];

        return response($response,200);
    }

    public function getStockOut(Request $request){
        $data = Stocktransaction::where('nonmoney','out');
        
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
            $data = $data->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $data = $data->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
        }

        $data = $data->get();

        $response = [
            'success'=>true,
            'stocktransaction'=>$data,
        ];

        return response($response,200);
    }

    public function getStockTransactionDetail(Request $request){
        $data = Stocktransaction::where('id',$request->id)->with('contact','substocktransaction','substocktransaction.product')->get();
        
        $response = [
            'success'=>true,
            'stocktransaction'=>$data,

        ];
        
        return response($response,200);
    }
    
    public function createStockIn(Request $request){

        $request->validate([
            'staff' =>'required',
            'date' =>'required',

            'product_id.*' =>'required',
            'qty.*'  =>'required',
            'purchase_price.*'  =>'required',
            'total.*'  =>'required|numeric',
        ]); 

        
        $stock = new Stocktransaction;
        $stock->staff = $request->staff;
        $stock->date = date("Y-m-d", strtotime($request->date));

        $stock->nonmoney = 'in';
        
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

        $akun = Akun::where('name','=','Persediaan Barang')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total + $total;
        $akun->save();

        $akun = Akun::where('name','=','Pendapatan Barang')->first();
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
            'staff' =>'required',
            'date' =>'required',

            'product_id.*' =>'required',
            'qty.*'  =>'required',
        ]); 

        $contact = Contact::where('id',$request->contact_id)->first();
        $sum = 0;

        $stock = new Stocktransaction;
        $stock->staff = $request->staff;
        $stock->date = date("Y-m-d", strtotime($request->date));
        $stock->nonmoney = 'out';

        $stock->save();

        $data = $request->product_id;
        $total = 0;
        $totalhpp=0;
        $lasthb=0;
        foreach ( $data as $key => $value) {
            $sub = new Substocktransaction;
            $sub->stocktransaction_id = $stock->id;
            $sub->product_id = $request->product_id[$key];
            $sub->qty = $request->qty[$key];
            $sub->save();

            $substocktransaction[]= $sub;

            $product = Product::find($sub->product_id);
            $product->qty = $product->qty - $sub->qty;
            $product->save();
            
            $qty = $sub->qty;
            $subin = Substocktransaction::where('left','>',0)
            ->whereHas('stocktransaction',function($query){
                $query->whereNull('pending');
            })->where('product_id','=',$sub->product_id)->get();
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
                
                $lasthb=$value->purchase_price;
            }
            $sibin = Substocktransaction::find($sub->id);
            $sibin->hpp = $totalhpp;
            $sibin->save();
            $total = $total + $sub->total;     
        }
              
        if ($qty > 0) {
            $totalhpp = $totalhpp + ($lasthb * $qty);
        }
        
        $akun = Akun::where('name','=','Persediaan Barang')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total - $totalhpp;
        $akun->save();

        $akun = Akun::where('name','=','Kerugian Barang Keluar Tanpa Penjualan')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total + $totalhpp;
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

    public function deleteStockTransaction(Request $request){

        $stock = Stocktransaction::find($request->id);
        if ($stock->nonmoney == "out") {

            $sub =Substocktransaction::where('stocktransaction_id','=',$stock->id)->get();
            $totalhpp=0;
            foreach ($sub as $key => $value) {
                $product = Product::find($value->product_id);
                $product->qty = $product->qty + $value->qty;
                $product->save(); 

                $totalhpp = $totalhpp + $value->hpp;
                $qty = $value->qty;
                $subin = Substocktransaction::whereNotNull('purchase_price')->where('product_id','=',$product->id)->orderBy('id','desc')->get();
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

            $akun = Akun::where('name','=','Kerugian Barang Keluar Tanpa Penjualan')->first();
            $akun = Akun::find($akun->id);
            $akun->total = $akun->total - $totalhpp;
            $akun->save();

           Substocktransaction::where('stocktransaction_id','=',$stock->id)->delete();

        }elseif ($stock->nonmoney == "in") {

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

            $akun = Akun::where('name','=','Pendapatan Barang')->first();
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
