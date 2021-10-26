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
    public function AkunReportLaba(Request $request){
        Akun::whereNotNull('name')->update(array('total' => 0));

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
                $stock = $stock->whereNotNull('cashin_id')->where('pending','<>',false);
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
                $stock = $stock->whereNotNull('cashout_id')->where('pending','<>',false);
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

        // PENDAPATAN
        $jasa = Substocktransaction::whereHas('product',function($product){
            $product->where('category','service');
        })->whereHas('stocktransaction',function($stock) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $stock = $stock->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
            }
            $stock = $stock->whereNotNull('cashin_id')->whereNull('pending');
        })->sum('total');

        $penjualan = Substocktransaction::whereHas('product',function($product){
            $product->where('category','<>','service');
        })->whereHas('stocktransaction',function($stock) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $stock = $stock->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
            }
            $stock = $stock->whereNotNull('cashin_id')->whereNull('pending');
        })->sum('total');

        $barang = Substocktransaction::whereHas('product',function($product){
            $product->where('category','<>','service');
        })->whereHas('stocktransaction',function($stock) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $stock = $stock->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
            }
            $stock = $stock->where('nonmoney','in');
        })->sum('total');

        $barangrugi = Substocktransaction::whereHas('product',function($product){
            $product->where('category','<>','service');
        })->whereHas('stocktransaction',function($stock) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $stock = $stock->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
            }
            $stock = $stock->where('nonmoney','out');
        })->sum('hpp');

        $potonganbeli = Stocktransaction::whereNotNull('cashout_id');
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $potonganbeli = $potonganbeli->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $potonganbeli = $potonganbeli->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
            }
        $potonganbeli = $potonganbeli->sum('discount');

        $potonganjual = Stocktransaction::whereNotNull('cashin_id');
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
            $potonganjual = $potonganjual->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $potonganjual = $potonganjual->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
        }
        $potonganjual = $potonganjual->sum('discount');

        $hpp = Substocktransaction::whereHas('product',function($product){
            $product->where('category','<>','service');
        })->whereHas('stocktransaction',function($stock) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $stock = $stock->whereBetween('date',[date('Y-m-01',time()),date('Y-m-d',time())]);
            }
            $stock = $stock->whereNotNull('cashin_id');
        })->sum('hpp');

        $akunJasa = Akun::where('name','=','Pendapatan Jasa')->first();
        $akunJasa->total = $jasa;

        $akunPenjualan = Akun::where('name','=','Pendapatan Penjualan')->first();
        $akunPenjualan->total = $penjualan;

        $akunBarang = Akun::where('name','=','Pendapatan Barang')->first();
        $akunBarang->total = $barang;

        $akunBarangRugi = Akun::where('name','=','Kerugian Barang Keluar Tanpa Penjualan')->first();
        $akunBarangRugi->total = $barangrugi;

        $akunPotonganBeli = Akun::where('name','=','Potongan Pembelian')->first();
        $akunPotonganBeli->total = $potonganbeli;

        $akunHpp = Akun::where('name','=','Harga Pokok Penjualan')->first();
        $akunHpp->total = $hpp;

        $akunPotonganJual = Akun::where('name','=','Potongan Penjualan')->first();
        $akunPotonganJual->total = $potonganjual;
    
        //TOTAL KABEH
        $data = Akun::where('name',$request->name)->with(str_repeat('children.',10))->get();
        function akunRekursif($data,$total){
            foreach ($data as $key => $valuedata) {
                if (!empty($valuedata->children)) {
                    foreach ($total as $key => $valuetotal) {
                        if ($valuedata->name==$valuetotal->name) {
                            $valuedata->total = $valuedata->total + $valuetotal->total;
                        }
                    }
                    akunRekursif($valuedata->children,$total);
                }else{
                    foreach ($total as $key => $valuetotal) {
                        if ($valuedata->name==$valuetotal->name) {
                            $valuedata->total =$valuedata->total+ $valuetotal->total;
                        }
                    }
                }
            }
        }
        // masukin
        $akun=[];
        foreach ($cash as $key => $value) {
            array_push($akun,$value);
        }

        foreach ($cashin as $key => $value) {
            array_push($akun,$value);
        }

        foreach ($cashout as $key => $value) {
            array_push($akun,$value);
        }
        
        array_push($akun,$akunJasa);
        array_push($akun,$akunPenjualan);
        array_push($akun,$akunBarang);
        array_push($akun,$akunBarangRugi);
        array_push($akun,$akunPotonganBeli);
        array_push($akun,$akunPotonganJual);
        array_push($akun,$akunHpp);
        
        akunRekursif($data,$akun);


        $response = [
            'success'=>true,
            'akun'=>$data,
        ];

        return response($response,200);
    }

}
