<?php

namespace App\Http\Controllers;
use App\Models\Contact;
use App\Models\Contacttype;

use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function getContact(){
        $data = Contact::with('type')->get();
        
        $response = [
            'success'=>true,
            'contact'=>$data,
        ];
        
        return response($response,200);
    }

    public function getContactCustomer(){
        $data = Contact::whereHas('type', function($type){
            $type->where('category','=','customer');
        })->get();   

        $response = [
            'success'=>true,
            'contact'=>$data,
        ];
        
        return response($response,200);
    }

    public function getContactSupplier(){
        $data = Contact::whereHas('type', function($type){
                    $type->where('category','=','supplier');
                })->get();   

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
            'desc' =>'nullable',
            'address'  =>'nullable',
            'contact'  =>'nullable',
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
            'desc' =>'nullable',
            'address'  =>'nullable',
            'contact'  =>'nullable',
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
