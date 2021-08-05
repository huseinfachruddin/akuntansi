<?php

namespace App\Http\Controllers;
use App\Models\Supplier;

use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function getSupplier(){
        $data = Supplier::all();
        
        $response = [
            'success'=>true,
            'supplier'=>$data,
        ];
        
        return response($response,200);
    }

    public function getSupplierDetail(Request $request){
        $data = Supplier::find($request->id);
        
        $response = [
            'success'=>true,
            'supplier'=>$data,
        ];
        
        return response($response,200);
    }

    public function createSupplier(Request $request){
        $request->validate([
            'name' =>'required',
            'desc' =>'required',
            'address'  =>'required',
            'contact'  =>'required',
        ]);

        $data = new Supplier;
        $data->name = $request->name;
        $data->desc = $request->desc;
        $data->address = $request->address;
        $data->contact = $request->contact;
        $data->save();
        
        $response = [
            'success'=>true,
            'supplier'=>$data,
        ];
        
        return response($response,200);
    }

    public function editSupplier(Request $request){
        $request->validate([
            'name' =>'required',
            'desc' =>'required',
            'address'  =>'required',
            'contact'  =>'required',
        ]);

        $data = Supplier::find($request->id);
        $data->name = $request->name;
        $data->desc = $request->desc;
        $data->address = $request->address;
        $data->contact = $request->contact;
        $data->save();
        
        $response = [
            'success'=>true,
            'supplier'=>$data,
        ];
        
        return response($response,200);
    }

    public function deleteSupplier(Request $request){

        $data = Supplier::find($request->id);
        $data->delete();
        
        $response = [
            'success'=>true,
            'supplier'=>$data,
        ];
        
        return response($response,200);
    }

    
}
