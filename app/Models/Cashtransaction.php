<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Akun;
use App\Models\Subcashtransaction;

class Cashtransaction extends Model
{
    use HasFactory;
    protected $fillable = ['*'];

    public function from(){
        return $this->belongsTo(Akun::class,'from');
    }

    public function to(){
        return $this->belongsTo(Akun::class,'to');
    }

    public function subcashtransaction(){
        return $this->hasMany(Subcashtransaction::class,'cashtransaction_id');
    }





    
}
