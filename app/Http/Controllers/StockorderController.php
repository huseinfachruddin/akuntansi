<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stocktransaction;
use App\Models\Substocktransaction;
use App\Models\Product;
use App\Models\Akun;
use App\Models\Credit;
use App\Models\Contact;
use Carbon\Carbon;


class StockorderController extends Controller
{
    public function getStockOutDue(Request $request){

        $data = Stocktransaction::with('contact','cashin','cashout','substocktransaction','substocktransaction.product.unit','credit','credit.cashin')->whereNotNull('cashin_id')->where('pending',1);

        $data = $data->with('contact','cashout')->orderBy('date','ASC')->get();

        foreach ($data as $key => $value) {
            $day = date('Y-m-d',time());
            if ($value->date<$day) {
                $value->date = Carbon::create($value->date)->diffForHumans(null,true)." lagi";
            }else {
                $value->date = Carbon::create($value->date)->diffForHumans(null,true)." yang lalu";
            }
        }

        $response = [
            'success'=>true,
            'stocktransaction'=>$data,
        ];

        return response($response,200);
    }
    public function getStockTransactionDetail(Request $request){
    
        $data = Stocktransaction::where('id',$request->id)->with('contact','cashin','cashout','substocktransaction','substocktransaction.product.unit','credit','credit.cashin')->get();
        
        $response = [
            'success'=>true,
            'stocktransaction'=>$data,
        ];
        
        return response($response,200);
    }

    public function getStockIn(Request $request){
        
        $data = Stocktransaction::whereNotNull('cashout_id')->where('pending',1);
        
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
            $data = $data->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $data = $data->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
        }

        $data = $data->with('contact','cashout')->orderBy('date','DESC')->get();
        
        $response = [
            'success'=>true,
            'stocktransaction'=>$data,

        ];

        return response($response,200);
    }
   
    public function getStockOut(Request $request){
        $data = Stocktransaction::whereNotNull('cashin_id')->where('pending',1);

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
            $data = $data->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $data = $data->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
        }

        $data = $data->with('contact','cashin','credit')->orderBy('date','DESC')->get();
        
        $response = [
            'success'=>true,
            'stocktransaction'=>$data,

        ];

        return response($response,200);
    }

    public function createStockIn(Request $request){
        $request->validate([
            'contact_id' =>'required',
            'cashout_id' =>'nullable',
            'staff' =>'required',
            'paid' =>'required',
            'payment_due' =>'nullable',
            'date' =>'required',
            'discount' =>'nullable',

            'product_id.*' =>'required',
            'qty.*'  =>'required',
            'purchase_price.*'  =>'required',
            'total.*'  =>'required|numeric',
        ]); 
        if (empty($request->cashout_id)) {
            $request->cashout_id = Akun::where('iscash',true)->first()->id;
        }
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
        $stock->discount = $request->discount;
        $stock->save();

        $credit = new Credit;
        $credit->stocktransaction_id = $stock->id;
        $credit->cashout_id = $request->cashout_id;
        $credit->total = $stock->paid;
        $credit->save();

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
            'cashin_id' =>'nullable',
            'staff' =>'required',
            'paid' =>'required',
            'date' =>'required',
            'discount' =>'nullable',
            'date' =>'required',

            'payment_due' =>'nullable',
            'product_id.*'=>'required',
            'selling_price.*'=>'required',
            'qty.*'  =>'required',
            'total.*'  =>'required|numeric',
        ]);

        if (!empty($request->paid) || $request->paid>0) {
            $request->validate([
                'cashin_id' =>'required',
            ]); 
        }

        if (empty($request->cashin_id)) {
            $request->cashin_id = Akun::where('iscash',true)->first()->id;
        }

        $contact = Contact::with('type')->where('id',$request->contact_id)->first();
        $sum = 0;
        foreach ( $request->total as $key => $value) {
            $sum = $sum + $request->total[$key];
        }

        $hutang = ($sum -$request->paid) - $request->paid;

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
            $sub->selling_price = $request->selling_price[$key];
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
        $stock->discount = $request->discount;
        $stock->save();
        
        $akun = Akun::find($request->cashin_id);
        $akun->total = $akun->total + $stock->paid;
        $akun->save();

        $credit = new Credit;
        $credit->stocktransaction_id = $stock->id;
        $credit->cashin_id = $request->cashin_id;
        $credit->total = $stock->paid;
        $credit->save();
        
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
           $akun->total = ($akun->total-$akun->discount) - $stock->paid;
           $akun->save();

           Substocktransaction::where('stocktransaction_id','=',$stock->id)->delete();
        }elseif ($stock->cashout_id) {
            $akun = Akun::find($stock->cashout_id);
            $akun->total = $akun->total + $stock->paid;
            $akun->save();

            $totalhpp=0;

            $akun = Akun::where('name','=','Uang Muka Pesanan Pembelian')->first();
            $akun = Akun::find($akun->id);
            $akun->total = ($akun->total-$akun->discount) -  $stock->paid;
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
