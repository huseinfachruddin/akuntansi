<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Substocktransaction;
use App\Models\Contact;
use App\Models\Akun;
use App\Models\Credit;

class Stocktransaction extends Model
{
    protected $fillable = ['*'];
    use HasFactory;

    public function substocktransaction(){
        return $this->hasMany(Substocktransaction::class,'stocktransaction_id');
    }

    public function credit(){
        return $this->hasMany(Credit::class,'stocktransaction_id');
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

    public function getCreatedAtAttribute(){
        return \Carbon\Carbon::parse($this->attributes['created_at'])->format('Y-d-m H:i');
        }
    
    public function getUpdatedAtAttribute(){
            return \Carbon\Carbon::parse($this->attributes['updated_at'])->format('Y-d-m H:i');
        }

    public static function boot() {
            parent::boot();
    
            static::deleting(function($credit) { // before delete() method call this
                 $credit->credit()->delete();
                 // do the rest of the cleanup...
            });
        }
}
