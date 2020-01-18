<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table = 'drivers';
    protected $guarded = [];

    public function userInfo()
    {
        return $this->belongsTo('App\User');
    }

    public function miniDashboard()
    {
        return $this->belongsTo('App\Models\Dashboard\mini_dashboard');
    }
    public function online()
    {
        return  $this->join('users', 'users.id', '=', 'drivers.user_id')
                    ->select('users.name', 'drivers.lat', 'drivers.lng', 'users.id', 'drivers.busy', 'drivers.availability')
                    ->whereNotNull('drivers.deviceToken')
                    ->where('drivers.busy', 0)
                    ->where('drivers.availability', 'on');
    }

    public function offline()
    {
        return $this->join('users', 'users.id', '=', 'drivers.user_id')
                    ->select('users.name', 'drivers.lat', 'drivers.lng', 'users.id', 'drivers.busy', 'drivers.availability')
                    ->whereNotNull('drivers.deviceToken')
                    ->with('userInfo')
                    ->where('drivers.busy', 1)
                    ->where('drivers.availability', 'on');
    }
}
