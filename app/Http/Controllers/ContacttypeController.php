<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contacttype;
use App\Models\Contact;
use App\Models\Priceproduct;

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
            'name' =>'required|unique:contacttypes,name',
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
        $contact = Contact::where('type',$data->name)->update(array('name'=>$request->name,'maxdebt'=>$request->maxdebt,'category'=>$request->category));
        $price = Priceproduct::where('name',$data->name)->update(array('name'=>$request->name));
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
