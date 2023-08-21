<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderUpdates extends Model
{
    use HasFactory;
    protected $table = "order_updates";

    public $timestamps = false;
    
    const CREATED_AT = null;
    const UPDATED_AT = null;
}
