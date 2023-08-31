<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MerchantCredential extends Model
{
    use HasFactory;

    protected $fillable = ['settings'];
}
