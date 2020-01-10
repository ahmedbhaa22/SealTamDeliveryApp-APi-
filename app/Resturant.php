<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resturant extends Model
{
    protected $table = 'resturants';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
