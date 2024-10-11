<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleSheetAutoReplay extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'current_question',
        'next_question',
        'next_appointment',
        'value'
    ];

    protected $table = 'google_sheet_auto_replay';

    public function user(){
        return $this->belongsTo(SpUser::class,'user_id','id');
    }
}
