<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Licence;

class LicenceController extends Controller
{
    public function getLicence(Request $request){
        $data = Licence::first();
        if ($data) {
            if (base64_decode($data->company,true)&& base64_decode($data->address,true)) {
                $data->company = base64_decode($data->company);
                $data->address = base64_decode($data->address);
            }else{
                $data->company = base64_encode($request->company);
                $data->address = base64_encode($request->address);
            }
        }
        $response = [
            'success'=>true,
            'licence'=>$data,
        ];
        
        return response($response,200);
    }

    public function createLicence(Request $request){
        $request->validate([
            'licence' =>'required',
            'product_code' =>'required',
            'company' =>'required',
            'address'  =>'required',
        ]);
        $data = Licence::whereNotNull('id')->delete();
        
        $data = new Licence;
        $data->licence = $request->licence;
        $data->code = $request->product_code;
        $data->company = base64_encode($request->company);
        $data->address = base64_encode($request->address);
        $data->save();

        $response = [
            'success'=>true,
            'licence'=>$data,
        ];
        
        return response($response,200);
    }

    public function editLicence(Request $request){
        $request->validate([
            'licence' =>'required',
            'product_code' =>'required',
            'company' =>'required',
            'address'  =>'required',
        ]);
        $data = new Licence;
        $data->licence = $request->licence;
        $data->code = $request->product_code;
        $data->company = base64_encode($request->company);
        $data->address = base64_encode($request->address);
        $data->save();

        $response = [
            'success'=>true,
            'licence'=>$data,
        ];
        
        return response($response,200);
    }

    public function deleteLicence(Request $request){
        $data = Licence::whereNotNull('id')->delete();

        $response = [
            'success'=>true,
            'licence'=>$data,
        ];
        
        return response($response,200);
    }
}
