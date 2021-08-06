<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Producttype extends Model
{
    protected $fillable = ['*'];
    use HasFactory;

    public function product(){
        return $this->hasMany(Producttype::class,'producttype');
    }
}
