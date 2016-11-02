<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Place;
use Illuminate\Support\Facades\Auth;

class PlaceController extends Controller
{
    public function store(Request $request){
      $data = json_decode($request->all()['search-data']);

      $place = Place::find(['canonicalName',$data->canonicalName]);
      if(!($place)->count()){
        $place = new Place();
        foreach($data as $key => $val){
            $place->{$key} = $val;
        }
        $place->save();
      };


      $user = Auth::user();
      $user->follows()->save($place);
      //$user->follows()->delete($place);

      //dd($user->follows(),Auth::check());


      return redirect('/');
    }

}
