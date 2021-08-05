<?php

namespace App\Http\Controllers;
use App\Models\Contact;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function getContact(){
        $data = Contact::all();
        
        $response = [
            'success'=>true,
            'contact'=>$data,
        ];
        
        return response($response,200);
    }

    public function getContactDetail(Request $request){
        $data = Contact::find($request->id);
        
        $response = [
            'success'=>true,
            'contact'=>$data,
        ];
        
        return response($response,200);
    }

    public function createContact(Request $request){
        $request->validate([
            'name' =>'required',
            'desc' =>'required',
            'address'  =>'required',
            'contact'  =>'required',
            'type'  =>'required',
        ]);

        $data = new Contact;
        $data->name = $request->name;
        $data->desc = $request->desc;
        $data->address = $request->address;
        $data->contact = $request->contact;
        $data->type = $request->type;
        $data->save();
        
        $response = [
            'success'=>true,
            'contact'=>$data,
        ];
        
        return response($response,200);
    }

    public function editContact(Request $request){
        $request->validate([
            'name' =>'required',
            'desc' =>'required',
            'address'  =>'required',
            'contact'  =>'required',
            'type'  =>'required',
        ]);

        $data = Contact::find($request->id);
        $data->name = $request->name;
        $data->desc = $request->desc;
        $data->address = $request->address;
        $data->contact = $request->contact;
        $data->type = $request->type;
        $data->save();
        
        $response = [
            'success'=>true,
            'contact'=>$data,
        ];
        
        return response($response,200);
    }

    public function deleteContact(Request $request){

        $data = Contact::find($request->id);
        $data->delete();
        
        $response = [
            'success'=>true,
            'contact'=>$data,
        ];
        
        return response($response,200);
    }   
}
