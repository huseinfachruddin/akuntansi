<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Substocktransaction;
use App\Models\Contact;
use App\Models\Akun;

class Stocktransaction extends Model
{
    protected $fillable = ['*'];
    use HasFactory;

    public function substocktransaction(){
        return $this->hasMany(Substocktransaction::class,'stocktransaction_id');
    }

    public function contact(){
        return $this->belongsTo(Contact::class,'contact_id');
    }

    public function cashin(){
        return $this->belongsTo(Akun::class,'cashin_id');
    }

    public function cashout(){
        return $this->belongsTo(Akun::class,'cashout_id');
    }


}
