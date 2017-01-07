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
    /*
     * mandatory:
     *  type[json,geojson];
     */
    public function api(Request $request){
      $user = Auth::user();
      $atts = $request->all();
      $atts += ['filter'=> 'all'];

      $data = [];
      switch($atts['filter']){
        case 'all':
        default:

        $places = $user->follows()->get();
        $home = $user->home()->first();
        $map = (object)[];
        $data[] = (object)[
          'shortName'=>$home->shortName,
          'regionName'=>$home->regionName,
          'lat'=>$home->lat,
          'lng'=>$home->lng,
          'symbol'=>'building',
        ];

        foreach($places as $key => $place){
          $route =  $user->home()->first()->route()->edge($place);
          if(!$route)
            continue;
            //dd($place);
          $data[] = (object)[
            'shortName'=>$place->shortName,
            'regionName'=>$place->regionName,
            'lat'=>$place->lat,
            'lng'=>$place->lng,
            'symbol'=>'',
            'price'
          ];
        }
      }

      switch(strtolower($request->type)){
        case 'template':
          $result = $this->wrapTemplate($data);
        break;
        case 'geojson':
          $result = $this->wrapGeoJSON($data);
        break;
      }
      die($result);
    }

    private function wrapGeoJSON($data,$options = null){
      $geojson = (object)[
        'type'=> 'FeatureCollection',
        'features'=> []
      ];

      foreach($data as $item){
        //dd($item);
        $geojson->features[] = (object)[
          "type"=> "Feature",
          "geometry"=> (object)[
            "type"=> "Point",
            "coordinates"=> [$item->lng,$item->lat]
          ],
          "properties"=> (object)[
            "title"=> "Mapbox SF",
            "description"=> "155 9th St, San Francisco",
            'marker-color'=> '#f86767',
            'marker-size'=> 'large',
            'marker-symbol'=> $item->symbol,
          ]
        ];
      }

      return json_encode($geojson);
    }

    private function wrapTemplate($data){
      $result = (object)[
        'places'=>$data,
      ];

      return json_encode($result);
    }
}
