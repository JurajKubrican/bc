<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Place;

class HomeController extends Controller {

  /**
   * Create a new controller instance.
   *
   * @return void
   */
  public function __construct() {
    //$this->middleware('auth');
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Http\Response
   */
  public function index() {

    $user = Auth::user();
    if (!$user) {
      return redirect('/welcome');
    }
    if (!$user->home()->get()->count()) {
      return redirect('settings');
    }


    return view('home');
  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Http\Response
   */
  public function welcome() {
    
    return view('welcome');
  }

}
