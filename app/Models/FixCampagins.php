<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FixCampagins extends Model
{
    use HasFactory;
    
    protected  $table ="sp_whatsapp_schedules";

    public $timestamps = false;
    
    const CREATED_AT = null;
    const UPDATED_AT = null;
}
