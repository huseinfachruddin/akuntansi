<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Unit;

class UnitController extends Controller
{
    public function getUnit(){
        $data = Unit::all();
        
        $response = [
            'success'=>true,
            'unit'=>$data,
        ];
        
        return response($response,200);
    }

    public function getUnitDetail(Request $request){
        $data = Unit::find($request->id);
        
        $response = [
            'success'=>true,
            'unit'=>$data,
        ];
        
        return response($response,200);
    }

    public function createUnit(Request $request){
        $request->validate([
            'name' =>'required',
            'desc' =>'nullable',
        ]);

        $data = new Unit;
        $data->name = $request->name;
        $data->desc = $request->desc;
        $data->save();
        
        $response = [
            'success'=>true,
            'unit'=>$data,
        ];
        
        return response($response,200);
    }

    public function editUnit(Request $request){
        $request->validate([
            'name' =>'required',
            'desc' =>'nullable',
        ]);

        $data = Unit::find($request->id);
        $data->name = $request->name;
        $data->desc = $request->desc;
        $data->save();
        
        $response = [
            'success'=>true,
            'unit'=>$data,
        ];
        
        return response($response,200);
    }

    public function deleteUnit(Request $request){

        $data = Unit::find($request->id);
        $data->delete();
        
        $response = [
            'success'=>true,
            'unit'=>$data,
        ];
        
        return response($response,200);
    }
}
