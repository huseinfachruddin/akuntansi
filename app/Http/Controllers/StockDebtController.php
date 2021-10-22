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
use Illuminate\Support\Facades\DB;

class StockDebtController extends Controller
{
    public function getStockOutDebtDue(Request $request){

        $data = Stocktransaction::whereNotNull('cashin_id')
        ->whereNull('pending')
        ->whereRaw('paid < total')
        ->with('contact','cashin','credit')
        ->orderBy('payment_due','ASC')->get();
        foreach ($data as $key => $value) {
            $day = date('Y-m-d',time());
            if ($value->payment_due>$day) {
                $value->payment_due = Carbon::create($value->payment_due)->diffForHumans(null,true)." lagi";
            }else {
                $value->payment_due = Carbon::create($value->payment_due)->diffForHumans(null,true)." yang lalu";
            }
        }

        $response = [
            'success'=>true,
            'stocktransaction'=>$data,

        ];

        return response($response,200);
    }

    public function getStockOutDebt(Request $request){
        $data = Stocktransaction::whereNotNull('cashin_id')->whereNull('pending');

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
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

    public function paidStockOut(Request $request){
        $request->validate([
            'cashin_id' =>'required',
            'total' =>'required',
            'payment_due' =>'required',

        ]);
        
        $stock = Stocktransaction::find($request->id);
        if (($stock->total - $stock->discount)<($stock->paid + $request->total)) {
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
        if (($stock->total - $stock->discount)<($stock->paid + $request->total)) {
            return response(['error'=>'kelebihan dalam pembayaran'],400);
        }
        $stock->paid = $stock->paid + $request->total;
        $stock->payment_due = date("Y-m-d", strtotime($request->payment_due));
        $stock->save();

        $akun = Akun::find($request->cashout_id);
        $akun->total = $akun->total - $request->total;
        $akun->save();
        
        $akun = Akun::where('name','=','Hutang Pembelian Non Tunai')->first();
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

    public function deleteCreditTransaction(Request $request){

        $credit = Credit::find($request->id);
        $stock = Stocktransaction::find($credit->stocktransaction_id);

        if ($stock->cashin_id) { // Penjualan
            $stock = Stocktransaction::find($stock->id);
            $stock->paid = $stock->paid - $credit->total;
            $stock->save();

            $akun = Akun::where('name','=','Piutang Penjualan')->first();
            $akun = Akun::find($akun->id);
            $akun->total = $akun->total + $credit->total;
            $akun->save();

        }elseif ($stock->cashout_id){ // Pembelian

            $stock = Stocktransaction::find($stock->id);
            $stock->paid = $stock->paid - $credit->total;
            $stock->save();

            $akun = Akun::where('name','=','Hutang Pembelian Non Tunai')->first();
            $akun = Akun::find($akun->id);
            $akun->total = $akun->total + $credit->total;
            $akun->save();
        }
        
        $credit->delete();
        
        $response = [
            'success'=>true,
            'stocktransaction'=>$stock,
        ];

        return response($response,200);
    }

}
