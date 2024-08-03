<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $table = "sp_team";

    protected $fillable = ['pid','permissions','owner','ids'];

    public $timestamps = false;
    
    const CREATED_AT = null;
    const UPDATED_AT = null;

    public function account(){
        return $this->hasOne(Account::class,'team_id','id');
    }
}
