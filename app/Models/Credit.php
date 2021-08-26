<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Stocktransaction;
use App\Models\Akun;

class Credit extends Model
{
    protected $fillable = ['*'];
    use HasFactory;

    public function stocktransaction(){
        return $this->belongsTo(Stocktransaction::class,'stocktransaction_id');
    }

    public function cashin(){
        return $this->belongsTo(Akun::class,'cashin_id');
    }
}
