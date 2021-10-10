<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;

use App\Models\Cashtransaction;
use App\Models\Subcashtransaction;
use App\Models\Akun;

class CashController extends Controller
{
    public function getCash(){
        $data = Cashtransaction::with('from','to')->get();
        
        $response = [
            'success'=>true,
            'cashtransaction'=>$data,

        ];
        
        return response($response,200);
    }

    public function getCashIn(){
        $data = Cashtransaction::whereNotNull('cashin')->with('to')->get();
        
        $response = [
            'success'=>true,
            'cashtransaction'  =>$data,

        ];
        
        return response($response,200);
    }

    public function getCashOut(){
        $data = Cashtransaction::whereNotNull('cashout')->with('from')->get();
        
        $response = [
            'success'=>true,
            'cashtransaction'  =>$data,

        ];
        
        return response($response,200);
    }

    public function getCashTransfer(){
        $data = Cashtransaction::whereNotNull('transfer')->with('from','to')->get();
        
        $response = [
            'success'=>true,
            'cashtransaction'  =>$data,

        ];
        
        return response($response,200);
    }

    public function getCashTransactionDetail(Request $request){
        $data = Cashtransaction::where('id',$request->id)->with('from','to','subcashtransaction.akun')->get();
        
        $response = [
            'success'=>true,
            'cashtransaction'  =>$data,

        ];
        
        return response($response,200);
    }

   
    public function createCashIn(Request $request){
        $request->validate([
            'to' =>'required',
            'keterangan' =>'nullable',
            'staff' =>'nullable',
            'date' =>'required',

            'akun_id.*' =>'required',
            'desc.*'  =>'nullable',
            'total.*'  =>'required|numeric',
        ]);                

        $cash = new Cashtransaction;
        $cash->to = $request->to;
        $cash->staff = $request->staff;
        $cash->desc = $request->keterangan;
        $cash->date = $request->date;

        $cash->cashin = 0;
        $cash->save();

        $data = $request->akun_id;
        $total = 0;
        foreach ( $data as $key => $value) {
            $sub = new Subcashtransaction;
            $sub->cashtransaction_id = $cash->id;
            $sub->akun_id = $request->akun_id[$key];
            $sub->desc = null;
            $sub->total = $request->total[$key];
            $sub->save();

            $subtransaction[]= $sub;
            $akun = Akun::find($sub->akun_id);
            $akun->total = $akun->total + $request->total[$key];
            $akun->save();

            $total = $total + $request->total[$key];
            
        }

        
        $cash = Cashtransaction::find($cash->id);
        $cash->cashin = $cash->cashin + $total;
        $cash->save();
        
        $akun = Akun::find($request->to);
        $akun->total = $akun->total + $total;
        $akun->save();

        $response = [
            'success'=>true,
            'cashtransaction'  => $cash,
            'subcashtransaction'  =>$subtransaction,
        ];

        return response($response,200);
    }

    public function createCashOut(Request $request){
        $request->validate([
            'from' =>'required',
            'keterangan' =>'nullable',
            'staff' =>'nullable',
            'date' =>'required',

            'akun_id.*' =>'required',
            'desc.*'  =>'nullable',
            'total.*'  =>'required|numeric',
        ]);                

        $cash = new Cashtransaction;
        $cash->from = $request->from;
        $cash->staff = $request->staff;
        $cash->desc = $request->keterangan;
        $cash->date = $request->date;

        $cash->cashout = 0;
        $cash->save();

        $data = $request->akun_id;
        $total = 0;
        foreach ( $data as $key => $value) {
            $sub = new Subcashtransaction;
            $sub->cashtransaction_id = $cash->id;
            $sub->akun_id = $request->akun_id[$key];
            $sub->desc = $request->desc[$key];
            $sub->total = $request->total[$key];
            $sub->save();

            $subtransaction[]= $sub;
            $akun = Akun::find($sub->akun_id);
            $akun->total = $akun->total + $request->total[$key];
            $akun->save();

            $total = $total + $request->total[$key];
            
        }

        
        $cash = Cashtransaction::find($cash->id);
        $cash->cashout = $cash->cashout + $total;
        $cash->save();
        
        $akun = Akun::find($request->from);
        $akun->total = $akun->total - $total;
        $akun->save();

        $response = [
            'success'=>true,
            'cashtransaction'  => $cash,
            'subcashtransaction'  =>$subtransaction,
        ];

        return response($response,200);
    }

        public function createCashTransfer(Request $request){
        $request->validate([
            'to' =>'required',
            'from' =>'required',
            'date' =>'required',

            'desc' =>'nullable',
            'staff' =>'required',
            'total'  =>'required|numeric',
        ]);                

        $cash = new Cashtransaction;
        $cash->from = $request->from;
        $cash->to = $request->to;
        $cash->staff = $request->staff;
        $cash->desc = $request->desc;
        $cash->date = $request->date;
        $cash->transfer = $request->total;
        $cash->save();
        
        $akun = Akun::find($request->from);
        $akun->total = $akun->total - $request->total;
        $akun->save();

        $akun = Akun::find($request->to);
        $akun->total = $akun->total + $request->total;
        $akun->save();

        $response = [
            'success'=>true,
            'cashtransaction'  => $cash,
        ];

        return response($response,200);
    }

    public function deleteCashTransaction(Request $request){
        $data = Cashtransaction::find($request->id);
        if ($data->cashin) {
            $sub = Subcashtransaction::where('cashtransaction_id',$request->id)->get();

            $akun = Akun::find($data->to);
            $akun->total = $akun->total - $data->cashin;
            $akun->save();

            foreach ($sub as $key => $value) {
                $akun = Akun::find($value->akun_id);
                $akun->total = $akun->total - $value->total;
                $akun->save(); 
           }
           Subcashtransaction::where('cashtransaction_id',$request->id)->delete();
        }elseif($data->cashout) {
            $sub = Subcashtransaction::where('cashtransaction_id',$request->id)->get();

            $akun = Akun::find($data->from);
            $akun->total = $akun->total + $data->cashout;
            $akun->save();
            foreach ($sub as $key => $value) {
                $akun = Akun::find($value->akun_id);
                $akun->total = $akun->total - $value->total;
                $akun->save(); 
            }
            Subcashtransaction::where('cashtransaction_id',$request->id)->delete();
        }elseif($data->transfer){
            $akun = Akun::find($data->from);
            $akun->total = $akun->total + $data->transfer;
            $akun->save();

            $akun = Akun::find($data->to);
            $akun->total = $akun->total - $data->transfer;
            $akun->save();
        }
        
        $data->delete();
        $response = [
            'success'=>true,
            'cashtransaction'  => $data,
        ];
 
        return response($response,200);
    }

}
