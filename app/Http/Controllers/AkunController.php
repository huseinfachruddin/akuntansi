<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Akun;

class AkunController extends Controller
{
    
    public function getAkun(){
        $data =Akun::where('perent_id',null)->with(str_repeat('childern.',10))->get();

        $response = [
            'success'=>true,
            'akun'  =>$data,
        ];

        return response($response,200);
    }

    public function Report(){
        $data =Akun::whereNotNull('total')->Where('total','<>',0)->with(str_repeat('perent.',10))->get();

        $response = [
            'success'=>true,
            'akun'  =>$data,
        ];

        return response($response,200);
    }

    public function getAkunIsCash(){
        $data =Akun::where('iscash',true)->get();

        $response = [
            'success'=>true,
            'akun'  =>$data,
        ];

        return response($response,200);
    }

    public function getAkunNotCash(){
        $data =Akun::where('iscash',null)->orWhere('iscash',0)->get();

        $response = [
            'success'=>true,
            'akun'  =>$data,
        ];

        return response($response,200);
    }

    public function createAkun(Request $request){
        $request->validate([
            'perent_id' =>'nullable',
            'name' =>'required|unique:akuns',
            'iscash' =>'boolean',
            
        ]);

        $data = new Akun;
        $data->perent_id = $request->perent_id;
        $data->name = $request->name;
        $data->iscash = $request->iscash;
        $data->save();

        $response = [
            'success'=>true,
            'akun'  =>$data,
        ];

        return response($response,200);
    }

    public function editAkun(Request $request){
        $request->validate([
            'perent_id' =>'nullable',
            'name' =>'required|unique:akuns',
            'iscash' =>'boolean',
            
        ]);

        $data = Akun::find($request->id);
        $data->perent_id = $request->perent_id;
        $data->name = $request->name;
        $data->iscash = $request->iscash;
        $data->save();

        $response = [
            'success'=>true,
            'akun'  =>$data,
        ];

        return response($response,200);
    }

    public function deleteAkun(Request $request){

        $data = Akun::find($request->id);
        $data->delete();

        $response = [
            'success'=>true,
            'akun'  =>$data,
        ];

        return response($response,200);
    }
}
