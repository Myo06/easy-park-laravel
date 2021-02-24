<?php

namespace App\Http\Controllers;

use App\Exceptions\Handler;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class SearchParkingController extends Controller
{
    /*
    * test
    */
    public function browse(string $query)
    {
        $response = \GoogleMaps::load('textsearch')
                ->setParamByKey('query', 'parking+in+'.$query)
                ->setParamByKey('type', 'parking')
                ->get();
       return json_decode($response)->results;
    }
}
