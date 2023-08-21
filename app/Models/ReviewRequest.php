<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewRequest extends Model
{
    use HasFactory;
    protected $table = "review_requests";

    public $timestamps = false;
    
    const CREATED_AT = null;
    const UPDATED_AT = null;
}