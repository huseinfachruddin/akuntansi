<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\Cash;


class Cashintrans extends Model
{
    protected $fillable = [
        'code',
        'cash_id',
        'total'
    ];
    protected $guarded = [];

    use HasFactory;

    public function cash(){
        return $this->belongsTo(Cash::class);
    }

    public static function getCashInTransDetail($id = false){
        $query = DB::table('cashintrans as CIT');
        $query = $query->join('cashes as  C', 'C.id', '=', 'CIT.cash_id');
        $query = $query->select('CIT.id as id','CIT.code as code','C.name as to','CIT.total as total','CIT.created_at as date');
        if ($id) {
        $query = $query->where('C.id',$id);
        }
        $query = $query->get();

        return $query;
    }


}
