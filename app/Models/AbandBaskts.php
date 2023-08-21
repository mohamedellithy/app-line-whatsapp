<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbandBaskts extends Model
{
    use HasFactory;
    protected $table = "aband_baskts";

    public $timestamps = false;
    
    const CREATED_AT = null;
    const UPDATED_AT = null;
}
