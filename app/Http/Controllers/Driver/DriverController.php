<?php

namespace App\Http\Controllers\Driver;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Shared\BaseController;
use App\Helpers\HelperFunctions;
use DB;
use App\Resturant;
use App\Http\Resources\Driver\resturanMapResource;

class DriverController extends BaseController
{
    public function get_driver()
    {
        return $this->Response(true, auth()->user()->getCurrentDriverProfile());
    }

    public function change_category($id)
    {
        auth()->user()->driver->changeCategory($id);
        return $this->Response(true, null);
    }

    public function getResturantsMapPage()
    {
        $response = [
            'returants'=>auth()->user()->driver->getNerbyResturants()
        ];
        return $this->Response(true, $response);
    }
}
