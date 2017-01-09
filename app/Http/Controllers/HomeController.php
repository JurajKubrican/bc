<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Place;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $user = Auth::user();
      $places = $user->follows()->get();
      if(!$user->home()->get()->count())
        return redirect('settings');

      $home = $user->home()->first();

      foreach($places as $key => $place){
        $route =  $user->home()->first()->route()->edge($place);
        if(!$route)
          continue;
          //TODO:refresh graph
        $places[$key]->price = $route->price;
        $places[$key]->priceLow = $route->priceLow;
        $places[$key]->priceHigh = $route->priceHigh;

      }
      return view('home')
        ->with('places',$places);
        // ->with('map',json_encode($map));
    }
}
