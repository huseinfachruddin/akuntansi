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
        $cash = Akun::withCount(['creditin as sum_stockin' =>function($stock){
            $stock->select(DB::raw("SUM(total)"));
        },'creditout as sum_stockout' =>function($stock){
            $stock->select(DB::raw("SUM(total)"));
        },'cashtransactionfrom as sum_cashfrom' =>function($cash){
            $cash->select(DB::raw("SUM(cashout+transfer)"));
        },'cashtransactionto as sum_cashto' =>function($cash){
            $cash->select(DB::raw("SUM(cashin+transfer)"));
        }])->where('iscash',true)->get();
    
        foreach ($cash as $key => $value) {
            $value->total = ($value->sum_stockin - $value->sum_stockout)+($value->sum_cashto - $value->sum_cashfrom );
        }
        
        $cashin = Akun::withCount(['subcashtransaction as sum_subcash' =>function($sub){
            $sub->select(DB::raw("SUM(total)"));
        }])->where('iscashin',true)->get();

        foreach ($cashin as $key => $value) {
            $value->total = $value->sum_subcash;
        }
        
        $response = [
            'success'=>true,
            'cash'=>$cash,
            'cashin'=>$cashin
        ];

        return response($response,200);
    }
}
