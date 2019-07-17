<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    protected $table = 'admins';
   // protected $guarded = [];
    protected $fillable = [
        'user_id', 'AdminType'
    ];
}
