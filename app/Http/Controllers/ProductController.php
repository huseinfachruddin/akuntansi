<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Contact;
use App\Models\Contacttype;
use App\Models\Priceproduct;

class ProductController extends Controller
{
    public function getProductAll(Request $request){
        if (isset($request->contact_id)) {
            $data = Product::with('producttype','price','unit')->get();
            $customer = Contact::with('type')->where('id',$request->contact_id)->first();
            foreach ($data as $key => $value) {
                if (!empty($customer->type()->first())) {
                    $price = Priceproduct::where('product_id',$value->id)
                    ->where('name',$customer->type()->first()->name)
                    ->first();
                    if (!empty($price)) {
                        $value->selling_price=$price->total;
                    }
                }
            }
        }else{
            $data = Product::with('producttype','price','unit')->get();
        }

        $response = [
            'success'=>true,
            'product'=>$data,
        ];
        
        return response($response,200);
    }

    public function getProduct(Request $request){
        if (isset($request->contact_id)) {
            $data = Product::with('producttype','price','unit')->where('qty','>','0')->get();
            $customer = Contact::with('type')->where('id',$request->contact_id)->first();
            foreach ($data as $key => $value) {
                if (!empty($customer->type()->first())) {
                    $price = Priceproduct::where('product_id',$value->id)
                    ->where('name',$customer->type()->first()->name)
                    ->first();
                    if (!empty($price)) {
                        $value->selling_price=$price->total;
                    }
                }
            }
        }else{
            $data = Product::with('producttype','price','unit')->get();
        }

        $response = [
            'success'=>true,
            'product'=>$data,
        ];
        
        return response($response,200);
    }

    public function getProductGoods(Request $request){
        $data = Product::with('producttype','price','unit')->where('category','<>','service')->get();
        if (isset($request->contact_id)) {
            $customer = Contact::with('type')->where('id',$request->contact_id)->first();
            foreach ($data as $key => $value) {
                if (!empty($customer->type()->first())) {
                    $price = Priceproduct::where('product_id',$value->id)
                    ->where('name',$customer->type()->first()->name)
                    ->first();
                    if (!empty($price)) {
                        $value->selling_price=$price->total;
                    }
                }
            }
        }

        $response = [
            'success'=>true,
            'product'=>$data,
        ];
        
        return response($response,200);
    }

    public function getProductService(Request $request){
        $data = Product::with('producttype','price','unit')->where('qty','>',0)->where('category','service')->get();
        if (isset($request->contact_id)) {
            $customer = Contact::with('type')->where('id',$request->contact_id)->first();
            foreach ($data as $key => $value) {
                if (!empty($customer->type()->first())) {
                    $price = Priceproduct::where('product_id',$value->id)
                    ->where('name',$customer->type()->first()->name)
                    ->first();
                    if (!empty($price)) {
                        $value->selling_price=$price->total;
                    }
                }
            }
        }

        $response = [
            'success'=>true,
            'product'=>$data,
        ];
        
        return response($response,200);
    }

    public function getProductDetail(Request $request){
        $data = Product::find($request->id)->with('producttype','price')->first();

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
            'purchase_price'  =>'nullable',
            'selling_price'  =>'required',
            'producttype'  =>'nullable',
            'category'  =>'required',
        ]);

        $data = new Product;
        $data->code = 'P'.rand(100,999).time();
        $data->name = $request->name;
        $data->qty = 0;
        $data->unit = $request->unit;
        $data->selling_price = $request->selling_price;
        if ($request->category=='service') {
            $data->qty = 100;
        }else {
            $data->producttype = $request->producttype;
            $data->purchase_price = $request->purchase_price;
        }
        $data->category = $request->category;

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
            'purchase_price'  =>'nullable',
            'selling_price'  =>'required',
            'producttype'  =>'nullable',
            'category'  =>'required',
        ]);

        $data = Product::find($request->id);
        $data->name = $request->name;
        $data->unit = $request->unit;
        $data->selling_price = $request->selling_price;
        if ($request->category=='service') {
            $data->qty = 100;
        }else {
            $data->producttype = $request->producttype;
            $data->purchase_price = $request->purchase_price;
        }
        $data->category = $request->category;

        $data->save();
        
        $response = [
            'success'=>true,
            'product'=>$data,
        ];
        
        return response($response,200);
    }

    public function deleteProduct(Request $request){

        $data = Product::find($request->id);
        $data->price()->delete();
        $data->delete();

        $response = [
            'success'=>true,
            'product'=>$data,
        ];
        
        return response($response,200);
    }
}
