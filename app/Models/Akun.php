<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cashtransaction;
use App\Models\Subcashtransaction;
use App\Models\Stocktransaction;
use App\Models\Credit;
use Illuminate\Support\Facades\DB;

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

    public function stocktransactioncashin(){
        return $this->hasMany(Stocktransaction::class,'cashin_id');
    }

    public function stocktransactioncashout(){
        return $this->hasMany(Stocktransaction::class,'cashout_id');
    }

    public function perent(){
        return $this->belongsTo(Self::class,'perent_id');
    }

    public function creditin(){
        return $this->hasMany(Credit::class,'cashin_id');
    }

    public function creditout(){
        return $this->hasMany(Credit::class,'cashout_id');
    }

    public function children(){
        return $this->hasMany(Self::class,'perent_id');
    }

    public function getCreatedAtAttribute(){
        return \Carbon\Carbon::parse($this->attributes['created_at'])->format('Y-d-m H:i');
        }
    
    public function getUpdatedAtAttribute(){
            return \Carbon\Carbon::parse($this->attributes['updated_at'])->format('Y-d-m H:i');
        }
}
