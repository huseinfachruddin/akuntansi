<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;

use App\Models\Cash;
use App\Models\Akun;
use App\Models\Cashintrans;
use App\Models\Cashinakun;

class CashController extends Controller
{
    public function Cash(){
        $cash = Cash::getCash();
        
        $response = [
            'success'=>true,
            'cash'  =>$cash,

        ];
        
        return response($response,200);
    }

    public function CashInTotal(){
        $cash = Cash::getCashIn();
        
        $response = [
            'success'=>true,
            'cash'  =>$cash,
        ];
        
        return response($response,200);
    }
    public function CashDetail($id = null){
        $cash = Cash::getCashInDetail($id);
        $transaction = Cashintrans::getCashInTransDetail($id);
    
        $response = [
            'success'=>true,
            'cash'  =>$cash,
            'transaction'  =>$transaction,
    
        ];
    
        return response($response,200);
    }

    public function CashInCreate(Request $request){
        $request->validate([
            'to' =>'required',
            'akun_id.*' =>'required',
            'desc.*'  =>'required',
            'total.*'  =>'required|numeric',
        ]);                

        $cashintrans = new Cashintrans;
        
        $cashintrans->code = 'CIT'.rand().time();
        $cashintrans->cash_id = $request->to;
        $cashintrans->total = 0;
        $cashintrans->save();
        $data = $request->akun_id;
        $total = 0;
        foreach ( $data as $key => $value) {
            $cashinakun = Cashinakun::create([
                'cashin_id' => $cashintrans->id,
                'akun_id' => $request->akun_id[$key],
                'desc' => $request->desc[$key],
                'total' => $request->total[$key],
            ]);
            $total = $total+$request->total[$key];
            
        }
        $cashintrans->find($cashintrans->id);
        $cashintrans->total = $total;
        $cashintrans->save();

        $response = [
            'success'=>true,
            'cash'  =>$cashintrans,
            'transaction'  =>$cashinakun,
        ];

        return response($response,200);
    }

    public function Cashintrans($id = null){
        $transaction = Cashintrans::getCashInTransDetail();

        $response = [
            'success'=>true,
            'transaction'  =>$transaction,
        ];
        return response($response,200);
    }


}
