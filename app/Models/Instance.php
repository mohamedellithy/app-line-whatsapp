<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    use HasFactory;

    protected $table = "instances";

    public $timestamps = false;
    
    const CREATED_AT = null;
    const UPDATED_AT = null;
}
