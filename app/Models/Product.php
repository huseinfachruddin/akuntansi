<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Producttype;
use App\Models\Priceproduct;
use App\Models\Unit;
use App\Models\Substocktransaction;
use Illuminate\Support\Facades\DB;

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

    public static function total(){
        $query=DB::table('products')
        ->select(DB::raw('sum(qty*purchase_price) as total'))
        ->first();

        return $query;
    }

    public function price(){
        return $this->hasMany(Priceproduct::class,'product_id');
    }

    public function unit(){
        return $this->belongsTo(Unit::class,'unit');
    }

    public function getCreatedAtAttribute(){
        return \Carbon\Carbon::parse($this->attributes['created_at'])->format('Y-d-m H:i');
        }
    
    public function getUpdatedAtAttribute(){
            return \Carbon\Carbon::parse($this->attributes['updated_at'])->format('Y-d-m H:i');
        }

    

}
