<?php

namespace App\Models\Dashboard;

use Illuminate\Database\Eloquent\Model;

class permisions extends Model
{
    public $timestamps= false;
    protected $fillable = [
        'name',
        'type_id'
    ];

    protected $hidden = [
        'pivot'
    ];
    public function type()
    {
        return $this->belongsTo('App\Models\Dashboard\admin_types', "type_id");
    }
}
