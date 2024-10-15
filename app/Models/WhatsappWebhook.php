<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappWebhook extends Model
{
    use HasFactory;

    protected $table = "sp_whatsapp_webhook";

    protected $fillable = ['ids','team_id','instance_id','webhook_url','status'];

    public $timestamps = false;
    
    const CREATED_AT = null;
    const UPDATED_AT = null;
}
