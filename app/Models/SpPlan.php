<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpPlan extends Model
{
    use HasFactory;
    protected $table = "sp_plans";

    public $timestamps = false;
    
    const CREATED_AT = null;
    const UPDATED_AT = null;
}
