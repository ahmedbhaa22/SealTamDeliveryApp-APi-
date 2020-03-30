<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Helpers\HelperFunctions;

class Resturant extends Model
{
    protected $table = 'resturants';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function category()
    {
        return $this->belongsTo('App\Models\General\category', 'category_id');
    }

    public function getNearstDriverFornewOrder()
    {
        $Max_Drivers = Setting::find(4)->value ??0;
        $Zone_Size = Setting::find(5)->value ?? 0;
        if ($this->lat && $this->lng) {
            return HelperFunctions::get_in_range_entities($this->lat, $this->lng, new Driver(), 'drivers', 'lat', 'lng', $Zone_Size, $Max_Drivers)
            ->where('availability', 'on')
            ->where('canReceiveOrder', '1')
            ->where('busy', 0)
            ->whereNotNull('deviceToken')
            ->lockForUpdate()->get();
        }
        return [];
    }
}
