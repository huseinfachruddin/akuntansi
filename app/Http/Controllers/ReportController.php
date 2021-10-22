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

class ReportController extends Controller
{
    public function CashReport(Request $request){
        // CREDIT STOCK MASUK = menghitung uang masuk dari stock
        $cash = Akun::withCount(['creditin as sum_stockin' =>function($credit) use($request){
            $credit->whereHas('stocktransaction',function($stock) use($request){
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $request->start_date = date('Y-m-d',strtotime($request->start_date));
                    $request->end_date = date('Y-m-d',strtotime($request->end_date));
                    $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
                }else{
                    $stock = $stock->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
                }
                $stock = $stock->whereNull('pending')->orWhere('pending',1);
            })->select(DB::raw("SUM(total)"));
        },
        // CREDIT STOCK KELUAR = menghitung uang keluar dari stock
        'creditout as sum_stockout' =>function($credit) use($request){
            $credit->whereHas('stocktransaction',function($stock) use($request){
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $request->start_date = date('Y-m-d',strtotime($request->start_date));
                    $request->end_date = date('Y-m-d',strtotime($request->end_date));
                    $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
                }else{
                    $stock = $stock->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
                }
                $stock = $stock->whereNull('pending')->orWhere('pending',1);
            })->select(DB::raw("SUM(total)"));    
        },
        // CASH FROM = menghitung cash sebagai akun
        'cashtransactionfrom as sum_cashfrom' =>function($cash) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $cash = $cash->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $cash = $cash->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
            }
            $cash->select(DB::raw("SUM(cashout+transfer)"));
        },
        // CASH TO = menghitung cash sebagai akun
        'cashtransactionto as sum_cashto' =>function($cash) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $cash = $cash->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $cash = $cash->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
            }
            $cash->select(DB::raw("SUM(cashin+transfer)"));
        }])->where('iscash',true)->get();
    
        foreach ($cash as $key => $value) {
            $value->total = ($value->sum_stockin - $value->sum_stockout)+($value->sum_cashto - $value->sum_cashfrom );
        }
        // SUB CASH IN = menghitung cash sebagai akun
        $cashin = Akun::withCount(['subcashtransaction as sum_subcash' =>function($sub) use($request){
            $sub->select(DB::raw("SUM(total)"))->whereHas('cashtransaction',function($cash) use($request){
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $request->start_date = date('Y-m-d',strtotime($request->start_date));
                    $request->end_date = date('Y-m-d',strtotime($request->end_date));
                    $cash = $cash->whereBetween('date',[$request->start_date,$request->end_date]);
                }else{
                    $cash = $cash->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
                }
            });
            
        }])->where('iscashin',true)->get();
        
        foreach ($cashin as $key => $value) {
            $value->total = $value->sum_subcash;
        }
        // SUB CASH OUT
        $cashout = Akun::withCount(['subcashtransaction as sum_subcash' =>function($sub) use($request){
            $sub->select(DB::raw("SUM(total)"))->whereHas('cashtransaction',function($cash) use($request){
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $request->start_date = date('Y-m-d',strtotime($request->start_date));
                    $request->end_date = date('Y-m-d',strtotime($request->end_date));
                    $cash = $cash->whereBetween('date',[$request->start_date,$request->end_date]);
                }else{
                    $cash = $cash->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
                }
            });
        }])->where('iscashout',true)->get();

        foreach ($cashout as $key => $value) {
            $value->total = $value->sum_subcash;
        }

        $data = Akun::where('perent_id',null)->with(str_repeat('children.',10))->get();
        function akunRekursif($data,$total){
            foreach ($data as $key => $valuedata) {
                if ($valuedata->children==[]) {
                    foreach ($total as $key => $valuetotal) {
                        if ($valuedata->name==$valuetotal->name) {
                            $valuedata->total = $valuetotal->total;
                        }else{
                            $valuedata->total = 0;
                        }
                    }
                    akunRekursif($valuedata->children,$total);
                }else{
                    foreach ($total as $key => $valuetotal) {
                        if ($valuedata->name==$valuetotal->name) {
                            $valuedata->total = $valuetotal->total;
                        }else{
                            $valuedata->total = 0;
                        }
                    }
                }
            }
        }

        akunRekursif($data,$cash);
        akunRekursif($data,$cashin);
        akunRekursif($data,$cashout);

        $response = [
            'success'=>true,
            'report'=>$data,
        ];

        return response($response,200);
    }

    public function ReportLaba(Request $request){

        $biaya = Akun::withCount(['subcashtransaction as sum_subcash' =>function($sub) use($request){
            $sub->select(DB::raw("SUM(total)"))->whereHas('cashtransaction',function($cash) use($request){
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $request->start_date = date('Y-m-d',strtotime($request->start_date));
                    $request->end_date = date('Y-m-d',strtotime($request->end_date));
                    $cash = $cash->whereBetween('date',[$request->start_date,$request->end_date]);
                }else{
                    $cash = $cash->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
                }
            });
        }])->where('iscashout',true)->get();

        $jasa = Substocktransaction::whereHas('product',function($product){
            $product->where('category','service');
        })->whereHas('stocktransaction',function($stock){
            $stock->whereNull('pending');
        })->sum('total');

        $akun = Akun::where('name','=','Pendapatan Jasa')->first();
        $akun->total = 0;

        $data = Akun::where('perent_id',null)->with(str_repeat('children.',10))->get();
        function akunRekursif($data,$total){
            foreach ($data as $key => $valuedata) {
                if ($valuedata->children==[]) {
                    foreach ($total as $key => $valuetotal) {
                        if ($valuedata->name==$valuetotal->name) {
                            $valuedata->total = $valuetotal->total;
                        }else{
                            $valuedata->total = 0;
                        }
                    }
                    akunRekursif($valuedata->children,$total);
                }else{
                    foreach ($total as $key => $valuetotal) {
                        if ($valuedata->name==$valuetotal->name) {
                            $valuedata->total = $valuetotal->total;
                        }else{
                            $valuedata->total = 0;
                        }
                    }
                }
            }
        }

        akunRekursif($data,[$akun]);

        $response = [
            'success'=>true,
            'report'=>[$akun]  
        ];

        return response($response,200);
    }

}
