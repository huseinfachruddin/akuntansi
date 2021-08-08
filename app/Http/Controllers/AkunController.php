<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Akun;

class AkunController extends Controller
{
    private $total;
    public function test(){
          
        $data = Akun::with('children')->withSum('children','total')->get();
        $response = [
            'success'=>true,
            'data'  =>$data,
        ];

        return response($response,200);
    }

    public function getAkun(){
        $data =Akun::where('perent_id',null)->with(str_repeat('children.',10))->get();

        $response = [
            'success'=>true,
            'akun'  =>$data,
        ];

        return response($response,200);
    }

    public function getAkunList(){
        $data =Akun::all();

        $response = [
            'success'=>true,
            'akun'  =>$data,
        ];

        return response($response,200);
    }

    private function count_sum($data) {
        $this->total=0;
        foreach ($data as $key => $value) {
            $this->count_sum($data[$key]->children);
            $this->total += $data[$key]->total;
        }
                
        $data=$data->total+$this->total;
        return $data;
    }

    public function Report(Request $request){
        $data = Akun::where('name',$request->name)->with(str_repeat('children.',10))->get();
        $data = $this->count_sum($data);
        $response = [
            'success'=>true,
            'akun'  =>$data,
        ];

        return response($response,200);
    }

    public function reportName(Request $request){
        $data = Akun::where('name',$request->name)->with(str_repeat('children.',10))->get();

        $response = [
            'success'=>true,
            'akun'  =>$data,
        ];

        return response($response,200);
    }

    public function getAkunHead(Request $request){
        $data = Akun::where('name',$request->name)->with(str_repeat('children.',10))->get();
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
        $data =Akun::where('iscash',false)->orWhere('iscash',0)->get();

        $response = [
            'success'=>true,
            'akun'  =>$data,
        ];

        return response($response,200);
    }

        public function getAkunIsHeader(){
        $data =Akun::where('isheader',true)->orWhere('isheader',1)->get();

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
            'isheader' =>'boolean',
            'iscash' =>'boolean',
        ]);

        $data = new Akun;
        $data->perent_id = $request->perent_id;
        $data->name = $request->name;
        $data->isheader= $request->isheader;
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
            'name' =>'required',
            'isheader' =>'boolean',
            'iscash' =>'boolean',
            
        ]);

        $data = Akun::find($request->id);
        $data->perent_id = $request->perent_id;
        $data->name = $request->name;
        $data->isheader= $request->isheader;
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

        Akun::where('perent_id', '=', $data->id)->update(array('perent_id' => null));
        $data->delete();

        $response = [
            'success'=>true,
            'akun'  =>$data   ,
        ];

        return response($response,200);
    }
}
