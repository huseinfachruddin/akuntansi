<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products;

class Unit extends Model
{
    protected $fillable = ['*'];
    use HasFactory;

    public function product(){
        return $this->hasMany(Products::class,'unit');
    }
}
