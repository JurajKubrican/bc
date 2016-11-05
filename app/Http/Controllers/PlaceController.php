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

      //validation
      if( empty($data->canonicalName) ){
      return redirect('/');
      }

      //find or insert
      $place = Place::where( 'canonicalName' , $data->canonicalName )->first();
      if(!$place){
        $place = new Place();
        foreach($data as $key => $val){
            $place->{$key} = $val;
        }
        $place->save();
      };

      $user = Auth::user();
      //link to user
      if(!$user->follows()->get()->where('canonicalName' , $data->canonicalName)->first()){
          $user->follows()->save($place);
      }

      //link to home
      $home = $user->home()->first();
      if(!$home->route()->get()->where('canonicalName' , $data->canonicalName)->first()){
          $home->route()->save($place);
      }



      return redirect('/');
    }

    public function index(){
        return redirect('/');
    }

    public function destroy( $id){
      $user = Auth::user();
      $place = $user->follows()->get()->find($id);
      if($place){
          $user->follows()->edge($place)->delete();
      }else{
        //TODO:it does not follows
      }
      return redirect('/');
    }


    /**
    * crawler entry point
    */
    private function r2rRoute($from,$to){

      
      dd($from,$to);

    }

    public function crawl(){
      $allPlaces = Place::all();

      foreach($allPlaces as $place){
        $allRoutes = $place->route()->get();
        //if($place->route()->get()->count())
        //dd($place->route()->get(),$place->route()->getEdge()->related());
        foreach($allRoutes as $dest){
          $edge = $place->route()->edge($dest);
          $response = $this->r2rRoute($place->canonicalName,$dest->canonicalName);
        }
      }
    }


}
