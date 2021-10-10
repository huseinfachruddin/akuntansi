<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function getProduct(){
        $data = Product::with('producttype')->get();
        
        $response = [
            'success'=>true,
            'product'=>$data,
        ];
        
        return response($response,200);
    }

    public function getProductDetail(Request $request){
        $data = Product::find($request->id)->with('producttype')->first();
        
        $response = [
            'success'=>true,
            'product'=>$data,
        ];
        
        return response($response,200);
    }

    public function createProduct(Request $request){
        $request->validate([
            'name' =>'required',
            'unit'  =>'required',
            'purchase_price'  =>'required',
            'selling_price'  =>'required',
            'producttype'  =>'required',
        ]);

        $data = new Product;
        $data->code = 'P'.rand(100,999).time();
        $data->name = $request->name;
        $data->qty = 0;
        $data->unit = $request->unit;
        $data->purchase_price = $request->purchase_price;
        $data->selling_price = $request->selling_price;
        $data->producttype = $request->producttype;
        $data->save();
        
        $response = [
            'success'=>true,
            'product'=>$data,
        ];
        
        return response($response,200);
    }

    public function editProduct(Request $request){
        $request->validate([
            'name' =>'required',
            'unit'  =>'required',
            'purchase_price'  =>'required',
            'selling_price'  =>'required',
            'producttype'  =>'required',
        ]);

        $data = Product::find($request->id);
        $data->name = $request->name;
        $data->unit = $request->unit;
        $data->purchase_price = $request->purchase_price;
        $data->selling_price = $request->selling_price;
        $data->producttype = $request->producttype;
        
        $data->save();
        
        $response = [
            'success'=>true,
            'product'=>$data,
        ];
        
        return response($response,200);
    }

    public function deleteProduct(Request $request){

        $data = Product::find($request->id);
        $data->delete();
        
        $response = [
            'success'=>true,
            'product'=>$data,
        ];
        
        return response($response,200);
    }
}
