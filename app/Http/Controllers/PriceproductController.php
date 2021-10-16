<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Priceproduct;

class PriceproductController extends Controller
{
    public function detailProductPrice(Request $request){
        $data = Priceproduct::where('product_id',$request->id)->get();
        $response = [
            'success'=>true,
            'price'=>$data,
        ];
        
        return response($response,200);
    }
    public function cratePrice(Request $request){
        $request->validate([
            'product_id' =>'required',
            'name'  =>'required',
            'total'  =>'required',
        ]);

        $data = new Priceproduct;
        $data->product_id = $request->product_id;
        $data->name = $request->name;
        $data->total = $request->total;
        $data->save();

        $response = [
            'success'=>true,
            'price'=>$data,
        ];
        
        return response($response,200);
    }

    public function deletePrice(Request $request){

        $data = Priceproduct::find($request->id);
        $data->delete();
        

        $response = [
            'success'=>true,
            'price'=>$data,
        ];
        
        return response($response,200);
    }
}
