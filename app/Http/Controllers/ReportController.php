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
        $cash = Akun::withCount(['creditin as sum_stockin' =>function($credit) use($request){
            $credit->whereHas('stocktransaction',function($stock) use($request){
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $request->start_date = date('Y-m-d',strtotime($request->start_date));
                    $request->end_date = date('Y-m-d',strtotime($request->end_date));
                    $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
                }else{
                    $stock = $stock->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
                }
            })->select(DB::raw("SUM(total)"));
        },'creditout as sum_stockout' =>function($credit) use($request){
            $credit->whereHas('stocktransaction',function($stock) use($request){
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $request->start_date = date('Y-m-d',strtotime($request->start_date));
                    $request->end_date = date('Y-m-d',strtotime($request->end_date));
                    $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
                }else{
                    $stock = $stock->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
                }
            })->select(DB::raw("SUM(total)"));    
        },'cashtransactionfrom as sum_cashfrom' =>function($cash) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $cash = $cash->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $cash = $cash->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
            }
            $cash->select(DB::raw("SUM(cashout+transfer)"));
        },'cashtransactionto as sum_cashto' =>function($cash) use($request){
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
        
        $cashin = Akun::withCount(['subcashtransaction as sum_subcash' =>function($sub) use($request){
            $sub->select(DB::raw("SUM(total)"))->whereHas('stocktransaction',function($stock) use($request){
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $request->start_date = date('Y-m-d',strtotime($request->start_date));
                    $request->end_date = date('Y-m-d',strtotime($request->end_date));
                    $stock = $cash->whereBetween('date',[$request->start_date,$request->end_date]);
                }else{
                    $stock = $cash->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
                }
            });
            
        }])->where('iscashin',true)->get();
        
        foreach ($cashin as $key => $value) {
            $value->total = $value->sum_subcash;
        }

        $cashout = Akun::withCount(['subcashtransaction as sum_subcash' =>function($sub){
            $sub->select(DB::raw("SUM(total)"))->whereHas('stocktransaction',function($stock) use($request){
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $request->start_date = date('Y-m-d',strtotime($request->start_date));
                    $request->end_date = date('Y-m-d',strtotime($request->end_date));
                    $stock = $cash->whereBetween('date',[$request->start_date,$request->end_date]);
                }else{
                    $stock = $cash->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
                }
            });
        }])->where('iscashout',true)->get();

        foreach ($cashout as $key => $value) {
            $value->total = $value->sum_subcash;
        }

        $response = [
            'success'=>true,
            'cash'=>$cash,
            'cashin'=>$cashin,
            'cashout'=>$cashout

        ];

        return response($response,200);
    }
}
