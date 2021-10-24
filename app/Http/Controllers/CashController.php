<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Validation\Validator;

use App\Models\Cashtransaction;
use App\Models\Subcashtransaction;
use App\Models\Akun;

class CashController extends Controller
{
    public function getCash(Request $request){

        $data = Cashtransaction::with('from','to');

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
            $data = $data->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $data = $data->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
        }

        $data =$data->get();
        
        $response = [
            'success'=>true,
            'cashtransaction'=>$data,

        ];
        
        return response($response,200);
    }

    public function getCashIn(Request $request){

        
        $data = Cashtransaction::whereNotNull('cashin')->whereNull('from');

        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
            $data = $data->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $data = $data->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
        }

        $data =$data->with('to')->get();
        
        $response = [
            'success'=>true,
            'cashtransaction'  =>$data,

        ];
        
        return response($response,200);
    }

    public function getCashOut(Request $request){

        $data = Cashtransaction::whereNotNull('cashout')->whereNull('to');
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
            $data = $data->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $data = $data->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
        }
        $data =$data->with('from')->get();
        
        $response = [
            'success'=>true,
            'cashtransaction'  =>$data,

        ];
        
        return response($response,200);
    }

    public function getCashTransfer(Request $request){
        $data = Cashtransaction::whereNotNull('transfer')->whereNotNull('from')->whereNotNull('to');
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
            $data = $data->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $data = $data->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
        }
        $data=$data->with('from','to')->get();
        
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
            'total.*'  =>'required|numeric',
        ]);                

        $cash = new Cashtransaction;
        $cash->to = $request->to;
        $cash->staff = $request->staff;
        $cash->desc = $request->keterangan;
        $cash->date = date("Y-m-d", strtotime($request->date));

        $cash->cashin = 0;
        $cash->save();

        $data = $request->akun_id;
        $total = 0;
        function rekursif($akun,$sub,$name){
            foreach ($akun as $key => $value) {
                if (!empty($value->children)) {
                    rekursif($value->children,$sub,$name);
                }
                if ($value->name==$name->name) {
                    $sub->total = -1*$sub->total;
                }
            }
        }
        foreach ( $data as $key => $value) {
            $sub = new Subcashtransaction;
            $sub->cashtransaction_id = $cash->id;
            $sub->akun_id = $request->akun_id[$key];
            $sub->desc = null;
            $sub->total = $request->total[$key];
            $akun = Akun::where('name','Harta')->with(str_repeat('children.',10))->get();
            $nama = Akun::find($sub->akun_id);

            rekursif($akun,$sub,$nama);
            $sub->save();

            $subtransaction[]= $sub;
            $akun = Akun::find($sub->akun_id);
            $akun->total = $akun->total + $request->total[$key];
            $akun->save();

            $total = $total + $request->total[$key];
            
        }

        
        $cash = Cashtransaction::find($cash->id);
        $cash->cashin = $cash->cashin + $total;
        $cash->cashout = 0 ; 
        $cash->transfer = 0 ; 
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
            'total.*'  =>'required|numeric',
        ]);                

        $cash = new Cashtransaction;
        $cash->from = $request->from;
        $cash->staff = $request->staff;
        $cash->desc = $request->keterangan;
        $cash->date = date("Y-m-d", strtotime($request->date));

        $cash->cashout = 0;
        $cash->save();

        $data = $request->akun_id;
        $total = 0;
        function rekursif($akun,$sub,$name){
            foreach ($akun as $key => $value) {
                if (!empty($value->children)) {
                    rekursif($value->children,$sub,$name);
                }
                if ($value->name==$name->name) {
                    $sub->total = -1*$sub->total;
                }
            }
        }
        foreach ( $data as $key => $value) {
            $sub = new Subcashtransaction;
            $sub->cashtransaction_id = $cash->id;
            $sub->akun_id = $request->akun_id[$key];
            $sub->total = $request->total[$key];
            $sub->desc = null;
            $akun = Akun::where('name','Modal')->with(str_repeat('children.',10))->get();
            $akun2 = Akun::where('name','Kewajiban')->with(str_repeat('children.',10))->get();
            $nama = Akun::find($sub->akun_id);

            rekursif($akun,$sub,$nama);
            rekursif($akun2,$sub,$nama);
            $sub->save();

            $subtransaction[]= $sub;
            $akun = Akun::find($sub->akun_id);
            $akun->total = $akun->total + $request->total[$key];
            $akun->save();

            $total = $total + $request->total[$key];
            
        }

        
        $cash = Cashtransaction::find($cash->id);
        $cash->cashout = $cash->cashout + $total;
        $cash->cashin = 0 ; 
        $cash->transfer = 0 ; 
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
        $cash->date = date("Y-m-d", strtotime($request->date));
        $cash->transfer = $request->total;
        $cash->cashout = 0 ; 
        $cash->cashin = 0 ; 
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
