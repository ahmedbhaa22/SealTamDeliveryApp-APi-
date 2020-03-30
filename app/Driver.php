<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\HelperFunctions;
use DB;
use App\Resturant;
use App\Http\Resources\Driver\resturanMapResource;

class Driver extends Model
{
    protected $table = 'drivers';
    protected $guarded = [];

    public function userInfo()
    {
        return $this->belongsTo('App\User', 'user_id');
    }

    public function miniDashboard()
    {
        return $this->belongsTo('App\Models\Dashboard\mini_dashboard');
    }

    public function changeCategory($categoty_id)
    {
        $this->category_id = $categoty_id;
        $this->save();
    }

    public function category()
    {
        return $this->belongsTo('App\Models\General\category', 'category_id');
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

    public function getNerbyResturants()
    {
        if ($this->lat && $this->lng) {
            $resturants= HelperFunctions::get_in_range_entities($this->lat, $this->lng, new Resturant(), 'resturants', 'lat', 'lng', 40)
            ->with('user')->with('category')->get();
            return resturanMapResource::collection($resturants);
        }
        return [];
    }
}
