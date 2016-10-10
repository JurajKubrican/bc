<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Place;

class PlaceController extends Controller
{
    public function index(){

      $place = Place::all();
      return $place;
    }
}
