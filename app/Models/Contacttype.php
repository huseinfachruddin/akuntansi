<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contact;

class Contacttype extends Model
{
    protected $fillable = ['*'];
    use HasFactory;

    public function contact(){
        return $this->hasMany(Contact::class,'type');
    }

    public function getCreatedAtAttribute(){
        return \Carbon\Carbon::parse($this->attributes['created_at'])->format('Y-d-m H:i');
        }
    
    public function getUpdatedAtAttribute(){
            return \Carbon\Carbon::parse($this->attributes['updated_at'])->format('Y-d-m H:i');
        }
}
