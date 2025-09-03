<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; 

class Skpd extends Model
{
    protected $table = 'skpd';  
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id','nama', 'singkatan', 'alamat', 'telepon', 'foto','unor_id', 'unor_induk_id', 'diatasan_id'
    ];
}
