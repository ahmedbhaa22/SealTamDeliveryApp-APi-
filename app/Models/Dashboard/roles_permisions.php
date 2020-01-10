<?php

namespace App\Models\Dashboard;

use Illuminate\Database\Eloquent\Model;

class roles_permisions extends Model
{
    public function permision()
    {
        return $this->belongsTo('App\Models\Dashboard\permisions', "role_id");
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Dashboard\roles', "permision_id");
    }
}
