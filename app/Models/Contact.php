<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Stocktransaction;

class Contact extends Model
{
    protected $fillable = ['*'];
    use HasFactory;

    public function stocktransaction(){
        return $this->hasMany(Stocktransaction::class,'contact_id');
    }
}
