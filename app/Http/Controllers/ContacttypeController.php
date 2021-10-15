<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contacttype;

class ContacttypeController extends Controller
{
    public function getContacttype(){
        $data = Contacttype::all();
        
        $response = [
            'success'=>true,
            'contacttype'=>$data,
        ];
        
        return response($response,200);
    }

    public function getContacttypeDetail(Request $request){
        $data = Contacttype::find($request->id);
        
        $response = [
            'success'=>true,
            'contacttype'=>$data,
        ];
        
        return response($response,200);
    }

    public function createContacttype(Request $request){
        $request->validate([
            'name' =>'required',
            'category' =>'nullable',
            'maxdebt' =>'nullable',
        ]);

        $data = new Contacttype;
        $data->name = $request->name;
        $data->category = $request->category;
        $data->maxdebt = $request->maxdebt;

        $data->save();
        
        $response = [
            'success'=>true,
            'contacttype'=>$data,
        ];
        
        return response($response,200);
    }

    public function editContacttype(Request $request){
        $request->validate([
            'name' =>'required',
            'maxdebt' =>'nullable',
            'category' =>'nullable',

        ]);

        $data = Contacttype::find($request->id);
        $data->name = $request->name;
        $data->category = $request->category;
        $data->maxdebt = $request->maxdebt;
        $data->save();
        
        $response = [
            'success'=>true,
            'contacttype'=>$data,
        ];
        
        return response($response,200);
    }

    public function deleteContacttype(Request $request){

        $data = Contacttype::find($request->id);
        $data->delete();
        
        $response = [
            'success'=>true,
            'contacttype'=>$data,
        ];
        
        return response($response,200);
    }}
