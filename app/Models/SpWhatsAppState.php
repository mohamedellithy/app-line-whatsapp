<?php namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpWhatsAppState extends Model {
    use HasFactory;
    protected $table = "sp_whatsapp_stats";

    public $timestamps = false;

    const CREATED_AT = null;
    const UPDATED_AT = null;
}
