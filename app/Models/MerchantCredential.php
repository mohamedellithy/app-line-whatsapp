<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantCredential extends Model
{
    use HasFactory;

    protected $fillable = ['settings','app_name','access_token','refresh_token','user_id','merchant_id','phone','store_id'];

    public function user(){
        return $this->belongsTo(User::class,'user_id','id');
    }
}
