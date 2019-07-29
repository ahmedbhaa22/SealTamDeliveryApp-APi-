<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
      protected $table = 'orders';
      protected $guarded = [];


       protected $hidden = [
        'arrived_at', 'received_at','delivered_at',
    ];



}
