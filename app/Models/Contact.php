<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Stocktransaction;
use App\Models\Contacttype;


class Contact extends Model
{
    protected $fillable = ['*'];
    use HasFactory;

    public function stocktransaction(){
        return $this->hasMany(Stocktransaction::class,'contact_id');
    }

    public function type(){
        return $this->belongsTo(Contacttype::class,'type');
    }

    public function getCreatedAtAttribute(){
        return \Carbon\Carbon::parse($this->attributes['created_at'])->format('Y-d-m H:i');
        }
    
    public function getUpdatedAtAttribute(){
            return \Carbon\Carbon::parse($this->attributes['updated_at'])->format('Y-d-m H:i');
        }
}
