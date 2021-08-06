<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Stocktransaction;
use App\Models\Product;

class Substocktransaction extends Model
{
    use HasFactory;
    protected $fillable = ['*'];

    public function stocktransaction(){
        return $this->belongsTo(Stocktransaction::class,'stocktransaction_id');
    }
    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }
}
