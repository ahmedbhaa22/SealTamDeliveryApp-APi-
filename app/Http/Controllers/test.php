<?php

namespace App\Http\Controllers;
use Response;
use Illuminate\Http\Request;

class test extends Controller
{
    public function get(){
        return Response::json(['sda']);
    }
}
