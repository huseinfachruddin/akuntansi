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
        $data = Akun::withCount(['creditin as sum_cashin' =>function($stock){
            $stock->select(DB::raw("SUM(total)"));
        },'creditout as sum_cashout' =>function($stock){
            $stock->select(DB::raw("SUM(total)"));
        },'cashtransactionfrom as sum_cashfrom' =>function($cash){
            $cash->select(DB::raw("SUM(cashout+transfer)"));
        },'cashtransactionto as sum_cashto' =>function($cash){
            $cash->select(DB::raw("SUM(cashin+transfer)"));
        }])->where('iscash',true)->get();

        foreach ($data as $key => $value) {
            $value->total = ($value->sum_cashin - $value->sum_cashout)+($value->sum_cashto - $value->sum_cashfrom );
        }
        $response = [
            'success'=>true,
            'stocktransaction'=>$data,
        ];

        return response($response,200);
    }
}
