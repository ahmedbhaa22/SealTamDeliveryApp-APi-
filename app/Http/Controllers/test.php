<?php

namespace App\Http\Controllers;

use Response;
use Illuminate\Http\Request;
use App\Models\Dashboard\permisions;
use App\Models\Dashboard\roles;
use App\Admin;

class test extends Controller
{
    public function get($moduleName, $type)
    {
        return Admin::where('user_id', 1461)->first()->permisions();
    }
}
