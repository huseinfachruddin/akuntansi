<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cashtransaction;
use App\Models\Akun;

class Subcashtransaction extends Model
{
    use HasFactory;
    protected $fillable = ["cashtransaction_id","akun_id","total","desc"];

    public function cashtransaction(){
        return $this->belongsTo(Cashtransaction::class,'cashtransaction_id');
    }

    public function akun(){
        return $this->belongsTo(Akun::class,'akun_id');
    }

    public function getCreatedAtAttribute(){
        return \Carbon\Carbon::parse($this->attributes['created_at'])->format('Y-d-m H:i');
        }
    
    public function getUpdatedAtAttribute(){
            return \Carbon\Carbon::parse($this->attributes['updated_at'])->format('Y-d-m H:i');
        }

}

