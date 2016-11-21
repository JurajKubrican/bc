<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Place;
use App\User;
use App\r2rSearch;
use Illuminate\Support\Facades\Auth;

class PlaceController extends Controller
{
    public function store(Request $request){
      $data = json_decode($request->all()['search-data']);

      //validation
      if( empty($data->canonicalName) ){
      return redirect('/');
      }

      $place = Place::findOrCreate($data);

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



    private function enqueueAll(){

      $allUsers = User::all();
      foreach($allUsers as $user){
        $home = $user->home()->first();
        if(!$home)
          continue;
        foreach($user->follows()->get() as $dest){
          if(!$home->queue()->edge($dest) ){
            $home->queue()->save($dest);
          }
        }
      }

    }

    public function crawl(){
      $this->enqueueAll();

      $allPlaces = Place::all();
      foreach($allPlaces as $place){
        $allQueued = $place->queue()->get();
        if(!$allQueued->count())
          continue;
        foreach($allQueued as $dest){
          $queue = $place->queue()->edge($dest);
          $search = new r2rSearch($place,$dest);

          $place->deleteRoutesTo($dest);

          foreach($search->getRoutes() as $route){
            $previous = $place;

            foreach($route->segments as $segment){
              $segmentPlace = Place::findOrCreate($segment);

              $segmentEdge = $previous->segment()->edge($segmentPlace);
              if(!$segmentEdge){
                $segmentEdge = $previous->segment()->save($segmentPlace);
              }
              //dd($segment);
              $previous = $segmentPlace;
            }
            if(!$previous->segment()->edge($dest)){
              $previous->segment()->save($dest);
            }
            $edge = $place->route()->save($dest);

            $edge->priceLow = $route->priceLow;
            $edge->priceHigh = $route->priceHigh;
            $edge->price = $route->price;
            //$edge->segments = $route->segments;
            $edge->save();
          }
          $queue->delete();
        }
      }
    }


}
