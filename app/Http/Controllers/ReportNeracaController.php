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

class ReportNeracaController extends Controller
{
    public function AkunReportNeraca(Request $request){
        Akun::whereNotNull('name')->update(array('total' => 0));

        // CREDIT STOCK MASUK = menghitung uang masuk dari stock
        $cash = Akun::withCount(['creditin as sum_stockin' =>function($credit) use($request){
            $credit->whereHas('stocktransaction',function($stock) use($request){
                if (!empty($request->start_date) && !empty($request->end_date)) {
                    $request->start_date = date('Y-m-d',strtotime($request->start_date));
                    $request->end_date = date('Y-m-d',strtotime($request->end_date));
                    $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
                }else{
                    $stock = $stock->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
                }
                $stock = $stock->whereNotNull('cashin_id')->whereNull('pending')->orWhere('pending',1);
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
                    $stock = $stock->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
                }
                $stock = $stock->whereNotNull('cashout_id')->whereNull('pending')->orWhere('pending',1);
            })->select(DB::raw("SUM(total)"));    
        },
        // CASH FROM = menghitung cash sebagai akun
        'cashtransactionfrom as sum_cashfrom' =>function($cash) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $cash = $cash->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $cash = $cash->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
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
                $cash = $cash->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
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
                    $cash = $cash->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
                }
                $cash->whereNotNull('to');
            });
        }])->get();
        
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
                    $cash = $cash->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
                }
                $cash->whereNotNull('from');
            });
        }])->get();

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
                $stock = $stock->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
            }
            $stock = $stock->whereNotNull('cashin_id')->whereNull('pending');
        })->sum('total');

        // penjualan 
        $penjualan = Substocktransaction::whereHas('product',function($product){
            $product->where('category','<>','service');
        })->whereHas('stocktransaction',function($stock) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $stock = $stock->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
            }
            $stock = $stock->whereNotNull('cashin_id')->whereNull('pending');
        })->sum('total');

        // PENDAPATAN barang
        $barang = Substocktransaction::whereHas('product',function($product){
            $product->where('category','<>','service');
        })->whereHas('stocktransaction',function($stock) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $stock = $stock->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
            }
            $stock = $stock->where('nonmoney','in');
        })->sum('total');

        // Kerugian barang
        $barangrugi = Substocktransaction::whereHas('product',function($product){
            $product->where('category','<>','service');
        })->whereHas('stocktransaction',function($stock) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $stock = $stock->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
            }
            $stock = $stock->where('nonmoney','out');
        })->sum('total');

        // Potongan beli
        $potonganbeli = Stocktransaction::whereNotNull('cashout_id');
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $potonganbeli = $potonganbeli->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $potonganbeli = $potonganbeli->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
            }
        $potonganbeli = $potonganbeli->sum('discount');

        // Potongan jual

        $potonganjual = Stocktransaction::whereNotNull('cashin_id');
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
            $potonganjual = $potonganjual->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $potonganjual = $potonganjual->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
        }
        $potonganjual = $potonganjual->sum('discount');

        // HPP

        $hpp = Substocktransaction::whereHas('product',function($product){
            $product->where('category','<>','service');
        })->whereHas('stocktransaction',function($stock) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $stock = $stock->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
            }
            $stock = $stock->whereNotNull('cashin_id');
        })->sum('hpp');

        // Piutang jual

        $piutangjual = Stocktransaction::whereNotNull('cashin_id');
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
            $piutangjual = $piutangjual->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $piutangjual = $piutangjual->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
        }
        $piutangjual = $piutangjual->sum('total')-$piutangjual->sum('discount')-$piutangjual->sum('paid');

        // persedian

        $persediaanmasuk = Substocktransaction::whereHas('product',function($product){
            $product->where('category','<>','service');
        })->whereHas('stocktransaction',function($stock) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $stock = $stock->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
            }
            $stock = $stock->whereNotNull('cashout_id')->orWhere('nonmoney','in')->whereNull('pending');
        })->sum('total');

        $persediaanhpp = Substocktransaction::whereHas('product',function($product){
            $product->where('category','<>','service');
        })->whereHas('stocktransaction',function($stock) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $stock = $stock->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
            }
            $stock = $stock->whereNotNull('cashin_id')->whereNull('pending');
        })->sum('hpp');

        $persediaankeluar = Substocktransaction::whereHas('product',function($product){
            $product->where('category','<>','service');
        })->whereHas('stocktransaction',function($stock) use($request){
            if (!empty($request->start_date) && !empty($request->end_date)) {
                $request->start_date = date('Y-m-d',strtotime($request->start_date));
                $request->end_date = date('Y-m-d',strtotime($request->end_date));
                $stock = $stock->whereBetween('date',[$request->start_date,$request->end_date]);
            }else{
                $stock = $stock->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
            }
            $stock = $stock->where('nonmoney','out');
        })->sum('hpp');

        $persediaan = $persediaanmasuk - ($persediaankeluar + $persediaanhpp);

        $uangmukabeli = Stocktransaction::whereNotNull('cashout_id')->where('pending',1);
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
            $uangmukabeli = $uangmukabeli->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $uangmukabeli = $uangmukabeli->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
        }
        $uangmukabeli = $uangmukabeli->sum('paid');

        $pesanjual = Stocktransaction::whereNotNull('cashin_id')->where('pending',1);
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
            $pesanjual = $pesanjual->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $pesanjual = $pesanjual->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
        }
        $pesanjual = $pesanjual->sum('paid');

        $hutangbeli = Stocktransaction::whereNotNull('cashout_id');
        if (!empty($request->start_date) && !empty($request->end_date)) {
            $request->start_date = date('Y-m-d',strtotime($request->start_date));
            $request->end_date = date('Y-m-d',strtotime($request->end_date));
            $hutangbeli = $hutangbeli->whereBetween('date',[$request->start_date,$request->end_date]);
        }else{
            $hutangbeli = $hutangbeli->whereBetween('date',[date('0000-01-01',time()),date('Y-m-d',time())]);
        }
        $hutangbeli = $hutangbeli->sum('total')-$hutangbeli->sum('discount')-$hutangbeli->sum('paid');


        //AKUN BERNAMA ;
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
        
        $akunPiutangJual = Akun::where('name','=','Piutang Penjualan')->first();
        $akunPiutangJual->total = $piutangjual;
        
        $akunPersediaan = Akun::where('name','=','Persediaan Barang')->first();
        $akunPersediaan->total = $persediaan;

        $akunPesanBeli = Akun::where('name','=','Uang Muka Pesanan Pembelian')->first();
        $akunPesanBeli->total = $uangmukabeli;

        $akunPesanJual = Akun::where('name','=','Hutang Pesanan Penjualan')->first();
        $akunPesanJual->total = $pesanjual;

        $akunPembelian = Akun::where('name','=','Hutang Pembelian Non Tunai')->first();
        $akunPembelian->total = $hutangbeli;
        //TOTAL KABEH
        $data = Akun::where('name',$request->name)->with(str_repeat('children.',10))->get();
        function akunRekursif($data,$total){
            foreach ($data as $key => $valuedata) {
                if (!empty($valuedata->children)) {
                    foreach ($total as $key => $valuetotal) {
                        if ($valuedata->name==$valuetotal->name) {
                            $valuedata->total = $valuedata->total+$valuetotal->total;
                        }
                    }
                    akunRekursif($valuedata->children,$total);
                }else{
                    foreach ($total as $key => $valuetotal) {
                        if ($valuedata->name==$valuetotal->name) {
                            $valuedata->total = $valuedata->total + $valuetotal->total;
                        }
                    }
                }
            }
        }
        function rekursifTotal($data){
            foreach ($data as $key => $value) {
                if (!empty($value->children)) {
                    rekursifTotal($value->children);
                    foreach ($value->children as $key => $value2) {
                        $value->total+=$value2->total;
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
        array_push($akun,$akunPiutangJual);
        array_push($akun,$akunPersediaan);
        array_push($akun,$akunPesanBeli);
        array_push($akun,$akunPesanJual);
        array_push($akun,$akunPembelian);
        $pdptn = Akun::where('name','Pendapatan')->with(str_repeat('children.',10))->get();
        $hpp = Akun::where('name','Hpp')->with(str_repeat('children.',10))->get();
        $biaya = Akun::where('name','Biaya')->with(str_repeat('children.',10))->get();
        akunRekursif($pdptn,$akun);
        akunRekursif($hpp,$akun);
        akunRekursif($biaya,$akun);
        $LTB = Akun::where('name','=','Laba Tahun Berjalan')->first();
        $LTB->total = dd($pdptn);
        array_push($akun,$LTB);

        akunRekursif($data,$akun);
        rekursifTotal($data);
        
        $response = [
            'success'=>true,
            'akun'=>$data,
        ];

        return response($response,200);
    }

}
