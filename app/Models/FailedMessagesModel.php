<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FailedMessagesModel extends Model
{
    use HasFactory;
    
    protected $table="sp_failed_messages";
}
