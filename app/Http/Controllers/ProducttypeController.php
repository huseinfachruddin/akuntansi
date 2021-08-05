<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Producttype;

class ProducttypeController extends Controller
{
    public function getProducttype(){
        $data = Producttype::all();
        
        $response = [
            'success'=>true,
            'producttype'=>$data,
        ];
        
        return response($response,200);
    }

    public function getProducttypeDetail(Request $request){
        $data = Producttype::find($request->id);
        
        $response = [
            'success'=>true,
            'producttype'=>$data,
        ];
        
        return response($response,200);
    }

    public function createProducttype(Request $request){
        $request->validate([
            'name' =>'required',
        ]);

        $data = new Producttype;
        $data->name = $request->name;
        $data->save();
        
        $response = [
            'success'=>true,
            'producttype'=>$data,
        ];
        
        return response($response,200);
    }

    public function editProducttype(Request $request){
        $request->validate([
            'name' =>'required',
        ]);

        $data = Producttype::find($request->id);
        $data->name = $request->name;
        $data->save();
        
        $response = [
            'success'=>true,
            'producttype'=>$data,
        ];
        
        return response($response,200);
    }

    public function deleteProducttype(Request $request){

        $data = Producttype::find($request->id);
        $data->delete();
        
        $response = [
            'success'=>true,
            'product'=>$data,
        ];
        
        return response($response,200);
    }
}
