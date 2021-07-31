<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cashinakun extends Model
{
    protected $fillable = [
        'cashin_id',
        'akun_id',
        'total',
        'desc'
    ];
    protected $guarded = [];

    use HasFactory;

    public static function getCashinAkunDetail($id = false){
        $query = DB::table('cashinakun as CIA');
        $query = $query->join('cashintrans as  CIT', 'CIT.id', '=', 'CIA.cash_id');
        $query = $query->join('akuns as  A', 'A.id', '=', 'CIA.akun_id');
        $query = $query->select('CIA.id as id','CIA.desc as desc','CIA.total as total','CIA.created_at as date');
        if ($id) {
        $query = $query->where('CID.id',$id);
        }
        $query = $query->get();

        return $query;
    }
}
