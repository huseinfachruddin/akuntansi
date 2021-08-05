<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Producttype extends Model
{
    use HasFactory;
    protected $fillable = ['*'];

    public function product(){
        return $this->hasMany(Producttype::class,'producttype');
    }
}
