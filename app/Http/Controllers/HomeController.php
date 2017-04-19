<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class HomeController extends Controller {

  /**
   * Create a new controller instance.
   *
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
    
    //phpinfo();
    //die;

    $user = Auth::user();
    if (!$user) {
      return redirect('/welcome')->with('user',[]);
    }
    if (!$user->home()->get()->count()) {
      return redirect('settings');
    }


    return view('home')->with('user',$user->id);
  }

  public function indexUser($id){

    return view('user')->with('user',$id);

  }


  public function indexCity($id){

    return view('place')->with('place',$id);

  }

  /**
   * Show the application dashboard.
   *
   * @return \Illuminate\Http\Response
   */
  public function welcome() {
    
    return view('welcome')->with('user',0);
  }

}
