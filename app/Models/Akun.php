<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cashtransaction;
use App\Models\Subcashtransaction;

class Akun extends Model
{
    protected $fillable = ['*'];
    use HasFactory;

    public function cashtransactionfrom(){
        return $this->hasMany(Cashtransaction::class,'from');
    }

    public function cashtransactionto(){
        return $this->hasMany(Cashtransaction::class,'to');
    }

    public function subcashtransaction(){
        return $this->hasMany(Subcashtransaction::class,'akun_id');
    }

        public function perent(){
        return $this->belongsTo(Self::class,'perent_id');
    }

    public function children(){
        return $this->hasMany(Self::class,'perent_id');
    }
}
