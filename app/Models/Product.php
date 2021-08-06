<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Producttype;
use App\Models\Substocktransaction;

class Product extends Model
{
    use HasFactory;
    protected $fillable = ['*'];

    public function producttype(){
        return $this->belongsTo(Producttype::class,'producttype');
    }

    public function substocktransaction(){
        return $this->hasMany(Substocktransaction::class,'product_id');
    }

    

}
