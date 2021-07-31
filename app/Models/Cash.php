<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\Cashintrans;

class Cash extends Model
{
    use HasFactory;

    public function Cashintranses(){
        return $this->hasMany(Cashintrans::class);
    }

    public static function getCash(){
        $data = DB::table('cashes as C')
                ->join('cashintrans as  CIT', 'C.id', '=', 'CIT.cash_id')
                ->select('C.id as id','C.name as name','C.desc as desc',DB::raw('SUM(CIT.total) as total_masuk'))
                ->groupBy('cash_id')    
                ->get();
                return $data;
    }

    public static function getCashIn(){
        $data = DB::table('cashes as C')
                ->join('cashintrans as  CIT', 'C.id', '=', 'CIT.cash_id')
                ->select(DB::raw('SUM(CIT.total) as total_kas_masuk'))
                ->get();
                return $data;
    }

    public static function getCashInDetail($id = false){
        $data = DB::table('cashes as C')
                ->join('cashintrans as  CIT', 'C.id', '=', 'CIT.cash_id')
                ->select('C.name as name','C.desc as desc',DB::raw('SUM(CIT.total) as total_masuk'))
                ->where('C.id', $id)
                ->groupBy('cash_id')
                ->get();
                return $data;
    }
}
