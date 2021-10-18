<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stocktransaction;
use App\Models\Substocktransaction;
use App\Models\Product;
use App\Models\Akun;
use App\Models\Credit;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;



class StockController extends Controller
{
    public function getStockReport(Request $request){
        $data = Product::whereHas('substocktransaction',function($sub) use($request){
            $sub->whereHas('stocktransaction',function($query) use($request){
                $stock->whereNotNull('cashin_id')->where('pending',false)->orWhere('pending',null);
                if (isset($request->start_date) && isset($request->end_date)) {
                    $request->start_date=date("Y-m-d", strtotime($request->start_date));
                    $request->end_date=date("Y-m-d", strtotime($request->end_date));
        
                    $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
                }else{
                    $stock = $stock->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
                }
            });
        })->withSum('substocktransaction','qty')->withSum('substocktransaction','total')->where('')->get();

        $response = [
            'success'=>true,
            'stock'=>$data,
        ];

        return response($response,200);
    }

    public function getStockTransaction(Request $request){
        $data = Stocktransaction::with('contact','cashin','cashout');

        if (isset($request->start_date) && isset($request->end_date)) {
            $request->start_date=date("Y-m-d", strtotime($request->start_date));
            $request->end_date=date("Y-m-d", strtotime($request->end_date));

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

    public function getStockIn(Request $request){
        
        $data = Stocktransaction::whereNotNull('cashout_id')->where('pending',false)->orWhere('pending',null);
        
        if (isset($request->start_date) && isset($request->end_date)) {
            $request->start_date=date("Y-m-d", strtotime($request->start_date));
            $request->end_date=date("Y-m-d", strtotime($request->end_date));
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
        $data = Stocktransaction::whereNotNull('cashin_id')->where('pending',false)->orWhere('pending',null);

        if (isset($request->start_date) && isset($request->end_date)) {
            $request->start_date=date("Y-m-d", strtotime($request->start_date));
            $request->end_date=date("Y-m-d", strtotime($request->end_date));
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

    public function getStockOutDontPaid(Request $request){
        $data = Stocktransaction::whereNotNull('cashin_id')->where('pending',false)->orWhere('pending',null);;

        if (isset($request->start_date) && isset($request->end_date)) {
            $request->start_date=date("Y-m-d", strtotime($request->start_date));
            $request->end_date=date("Y-m-d", strtotime($request->end_date));
            $data = $data->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $data = $data->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
        }

        $data = $data->whereRaw('total > paid')->with('contact','cashin','credit')->orderBy('created_at','DESC')->get();
        
        $response = [
            'success'=>true,
            'stocktransaction'=>$data,

        ];

        return response($response,200);
    }

    public function getStockTransactionDetail(Request $request){
        $data = Stocktransaction::where('id',$request->id)->with('contact','cashin','cashout','substocktransaction','substocktransaction.product','credit','credit.cashout','credit.cashin')->get();
        
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
            'discount' =>'nullable',
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
        $stock->pending = false;
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
        $akun->total = $akun->total - $stock->paid;
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

        $akun = Akun::where('name','=','Potongan Pembelian')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total + $request->discount;
        $akun->save();
        
        $stock = Stocktransaction::find($stock->id);
        $stock->discount = $stock->discount + $request->discount;
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
            'discount' =>'nullable',

            'product_id.*' =>'required',
            'qty.*'  =>'required',
            'total.*'  =>'required|numeric',
        ]); 

        $contact = Contact::where('id',$request->contact_id)->first();
        $sum = 0;
        foreach ( $request->total as $key => $value) {
            $sum = $sum + $request->total[$key];
        }
        $hutang = $sum - $request->paid;
        if ($hutang > $contact->type()->first()->maxdebt && $contact->type()->first()->maxdebt!=null) {
            return response(['error'=>'Hutang Melebihi maxmal hutang customer'],400);
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
        $stock->pending = false;
        $stock->date = date("Y-m-d", strtotime($request->date));
        $stock->paid = $request->paid;
        $stock->payment_due = date("Y-m-d", strtotime($request->payment_due));
        $stock->save();

        $data = $request->product_id;
        $total = 0;
        $jasa = 0;
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

            $product = Product::find($sub->product_id);
            if ($product->category=='service') {

                $akun = Akun::where('name','=','Pendapatan Jasa')->first();
                $akun = Akun::find($akun->id);
                $akun->total = $akun->total + $sub->total;
                $akun->save();
                $jasa = $jasa + $sub->total;
                $total = $total + $sub->total;
                continue;
            }
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

        $akun = Akun::where('name','=','Pendapatan Penjualan')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total + ($total-$jasa);
        $akun->save();

        $akun = Akun::where('name','=','Harga Pokok Penjualan')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total + $totalhpp;
        $akun->save();

        $akun = Akun::where('name','=','Potongan Penjualan')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total + $request->discount;
        $akun->save();

        $stock = Stocktransaction::find($stock->id);
        $stock->discount = $stock->discount + $request->discount;
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

    public function paidStockOut(Request $request){
        $request->validate([
            'cashin_id' =>'required',
            'total' =>'required',
            'payment_due' =>'required',

        ]);
        
        $stock = Stocktransaction::find($request->id);
        if ($stock->total<($stock->paid + $request->total)) {
            return response(['error'=>'kelebihan dalam pembayaran'],400);
        }
        $stock->paid = $stock->paid + $request->total;
        $stock->payment_due = date("Y-m-d", strtotime($request->payment_due));
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
        $credit->save();

        $response = [
            'success'=> true,
            'stockktransaction'=>$stock,
        ];

        return response($response,200);
    }

    public function paidStockIn(Request $request){
        $request->validate([
            'cashout_id' =>'required',
            'total' =>'required',
            'payment_due' =>'required',

        ]);
        
        $stock = Stocktransaction::find($request->id);
        if ($stock->total<($stock->paid + $request->total)) {
            return response(['error'=>'kelebihan dalam pembayaran'],400);
        }
        $stock->paid = $stock->paid + $request->total;
        $stock->payment_due = date("Y-m-d", strtotime($request->payment_due));
        $stock->save();

        $akun = Akun::find($request->cashout_id);
        $akun->total = $akun->total + $request->total;
        $akun->save();
        
        $akun = Akun::where('name','=','Hutang Pembelian Non Tunai')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total - $request->total;
        $akun->save();

        $akun = Akun::where('name','=','Piutang Penjualan')->first();
        $akun = Akun::find($akun->id);
        $akun->total = $akun->total - $request->total;
        $akun->save();

        $credit = new Credit;
        $credit->stocktransaction_id = $stock->id;
        $credit->cashout_id = $request->cashout_id;
        $credit->total = $request->total;
        $credit->save();

        $response = [
            'success'=> true,
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
            $akun->total = $akun->total - ($stock->total - $stock->paid);
            $akun->save();

            $sub =Substocktransaction::where('stocktransaction_id','=',$stock->id)->get();
            $totalhpp=0;
            $jasa=0;
            foreach ($sub as $key => $value) {
                $product = Product::find($value->product_id);
                if ($product->category=='service') {

                    $akun = Akun::where('name','=','Pendapatan Jasa')->first();
                    $akun = Akun::find($akun->id);
                    $akun->total = $akun->total - $sub->total;
                    $akun->save();
                    $jasa = $jasa - $sub->total;
                    continue;
                }
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

           $akun = Akun::where('name','=','Pendapatan Penjualan')->first();
           $akun = Akun::find($akun->id);
           $akun->total = $akun->total - $stock->total;
           $akun->save();

           $akun = Akun::where('name','=','Harga Pokok Penjualan')->first();
           $akun = Akun::find($akun->id);
           $akun->total = $akun->total - $totalhpp;
           $akun->save();

           $akun = Akun::where('name','=','Potongan Penjualan')->first();
           $akun = Akun::find($akun->id);
           $akun->total = $akun->total - $stock->discount;
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

            $akun = Akun::where('name','=','Potongan Pembelian')->first();
            $akun = Akun::find($akun->id);
            $akun->total = $akun->total - $stock->discount;
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
