<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stocktransaction;
use App\Models\Substocktransaction;
use App\Models\Product;
use App\Models\Akun;
use App\Models\Credit;
use App\Models\Contact;

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

    public function getStockIn(Request $request){
        
        $data = Stocktransaction::whereNotNull('cashout_id')->where('pending',1);
        
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $data = $data->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $data = $data->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
        }

        $data = $data->with('contact','cashout')->orderBy('created_at','DESC')->get();
        
        $response = [
            'success'=>true,
            'stocktransaction'=>$data,

        ];

        return response($response,200);
    }
   
    public function getStockOut(Request $request){
        $data = Stocktransaction::whereNotNull('cashin_id')->where('pending',1);

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $data = $data->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $data = $data->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
        }

        $data = $data->with('contact','cashin','credit')->orderBy('created_at','DESC')->get();
        
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
        $stock->pending = true;
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
            $sub->total = $request->total[$key];
            $sub->save();

            $substocktransaction[]= $sub;

            $total = $total + $request->total[$key];
        }      

        $akun = Akun::find($request->cashout_id);
        $akun->total = $akun->total - $stock->paid;
        $akun->save();

        $akun = Akun::where('name','=','Uang Muka Pesanan Pembelian')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total +  $stock->paid;
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
            'date' =>'required',
            'payment_due' =>'required',

            'product_id.*'=>'required',
            'qty.*'  =>'required',
            'total.*'  =>'required|numeric',
        ]); 

        $contact = Contact::with('type')->where('id',$request->contact_id)->first();
        $sum = 0;
        foreach ( $request->total as $key => $value) {
            $sum = $sum + $request->total[$key];
        }

        $hutang = $sum - $request->paid;
        if ($hutang > $contact->type()->first()->maxdebt && $contact->type()->first()->maxdebt!=null) {
            return response(['error'=>'Hutang melebihi maximal'],400);
        }

        $paydue = date("Y-m-d", strtotime($request->payment_due));
        $day = $contact->type()->first()->max_paydue;
        $max_patdue=date('Y-m-d',time()+(60*60*24*$day));
        if ($paydue > $max_patdue && $contact->type()->first()->max_paydue!=null) {
            return response(['error'=>'Jatuh tempo melebihi maximal'],400);
        }

        $stock = new Stocktransaction;
        $stock->contact_id = $request->contact_id;
        $stock->cashin_id = $request->cashin_id;
        $stock->staff = $request->staff;
        $stock->pending = true;
        $stock->date = date("Y-m-d", strtotime($request->date));
        $stock->paid = $request->paid;
        $stock->payment_due = date("Y-m-d", strtotime($request->payment_due));
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
            $sub->total = $request->total[$key];
            $sub->save();

            $substocktransaction[]= $sub;
            $total = $total + $sub->total;
            
        }

        $akun = Akun::where('name','=','Hutang Pesanan Penjualan')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total + $stock->paid;
        $akun->save();

        $stock = Stocktransaction::find($stock->id);
        $stock->total = $total;
        $stock->save();
        
        $akun = Akun::find($request->cashin_id);
        $akun->total = $akun->total + $stock->paid;
        $akun->save();

        $response = [
            'success'=>true,
            'stockktransaction'=>$stock,
            'substocktransaction'=>$substocktransaction,
        ];
        return response($response,200);
    }

    public function deleteStockTransaction(Request $request){
        $stock = Stocktransaction::find($request->id);
        if ($stock->cashin_id) {

            $akun = Akun::find($stock->cashin_id);
            $akun->total = $akun->total - $stock->paid;
            $akun->save();

           $akun = Akun::where('name','=','Hutang Pesanan Penjualan')->first();
           $akun = Akun::find($akun->id);
           $akun->total = $akun->total - $stock->paid;
           $akun->save();

           Substocktransaction::where('stocktransaction_id','=',$stock->id)->delete();
        }elseif ($stock->cashout_id) {
            $akun = Akun::find($stock->cashout_id);
            $akun->total = $akun->total + $stock->total;
            $akun->save();

            $totalhpp=0;

            $akun = Akun::where('name','=','Uang Muka Pesanan Pembelian')->first();
            $akun = Akun::find($akun->id);
            $akun->total = $akun->total -  $stock->paid;
            $akun->save();

           Substocktransaction::where('stocktransaction_id','=',$stock->id)->delete();
        }
        $stock->delete();
        $stock->credit()->delete();
        
        $response = [
            'success'=>true,
            'stocktransaction'=>$stock,
        ];

        return response($response,200);
    }
}
