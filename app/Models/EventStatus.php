<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventStatus extends Model
{
    use HasFactory;

    protected $table = "event_status";

    protected $fillable = [
        'unique_number','values','type','event_from','status','count_of_call'
    ];
}
