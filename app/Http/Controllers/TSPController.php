<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\User;
use App\Place;
use Illuminate\Support\Facades\Auth;

class TSPController extends Controller {

  private $minCost = PHP_INT_MAX;
  private $routeCache = [[]];

  private function getRoutePrice($from,$to){
    if(isset($this->routeCache[$from->id][$to->id])){
      return $this->routeCache[$from->id][$to->id];
    }else{
      $route = $from->routes()->edge($to);
      if (!$route){
        PlaceController::fetchMissing($from,$to);
        $route = $from->routes()->edge($to);
      }

      $this->routeCache[$from->id][$to->id] = $route->minPrice;
      return $route->minPrice;
    }
  }


  public function run(){


    $user = empty($_GET['user']) ? Auth::user() : User::find($_GET['user']) ;
    if(!$user)
      return false;



    set_time_limit(5 * 60);

    $home = $user->home()->first();


    $places = $user->tsp()->get();

    if(!$places->count()){
      return json_encode([]);
    }

    $aPlaces = [];

    foreach($places as $place){
      $aPlaces[$place->id] = $place;

    }

    $cacheKey = $home->id.implode($aPlaces);
    if(!isset($user->tspCache)){
      $user->tspCache = serialize([]);
      $user->save();
    }

      $cache = unserialize($user->tspCache);

    if( isset($cache[$cacheKey])){
      $path = $cache[$cacheKey];
    }else{
      $path = $this->tsp($home,$aPlaces,$home);
      $cache[$cacheKey] = $path;
      $user->tspCache = serialize($cache);
      $user->save();
    }



    array_push($path->path,$home);
    if(isset($_GET['type']) && $_GET['type']==='template'){
      $places = $path->path;
      $newPlaces = [];
      foreach ($places as $key =>$place){
        $newPlaces[] = (object)[
          'item1' => $key+1,
          'item2' => $place->shortName,
          'item3' => '',
        ];
        if(!isset($places[$key+1])){
          $newPlaces[] = (object)[
            'item1' => '<b>TOTAL</b>',
            'item2' => '',
            'item3' => '<b>'.$path->cost.'€</b>',
          ];
          break;
        }


        $routes = json_decode($place->routes()->edge($places[$key+1])->routes);
        $min = PHP_INT_MAX;
        $chosen = false;
        foreach($routes as $route ){
          if($route->priceLow < $min && $route->priceLow !== 0){
            $min =$route->priceLow ;
            $chosen = $route;
          }
        }

        $newPlaces[] = (object)[
          'item1' => '',
          'item2' => $chosen->typeName,
          'item3' => $chosen->priceLow.'€',
        ];

      }


      die(json_encode((object) [
        'places' => $newPlaces,
      ]));
    }else{
      die($this->toGeoJson($path->path));
    }


  }


  private function tsp($from, $left, $home){



    if(empty($left)){

      return (object)[
        'cost' => $this->getRoutePrice($from,$home),
        'path' => [$home]
      ];

    }


    $minCost = PHP_INT_MAX;
    $path = [];
    foreach($left as $to){


      $newLeft = $left;
      unset($newLeft[$to->id]);

      $routePrice = $this->getRoutePrice($from,$to);

      $tspResult = $this->tsp($to, $newLeft, $home);
      if(!$tspResult || !is_array($tspResult->path))
        continue;

      $newCost  = $tspResult->cost + $routePrice;

      if($newCost < $minCost && $tspResult)
      {

        $minCost = $newCost;

        array_push($tspResult->path,$to);
        $path = $tspResult->path;

      }
    };

    return (object)[
      'cost' => $minCost,
      'path' => $path
    ];
  }



  private function toGeoJson($path){
    $line = [];

    $geojson = (object) [
      'type' => 'FeatureCollection',
      'features' => []
    ];

    foreach ($path as $key => $item) {
//      dd($item);
      $line[] = [$item->lng,$item->lat];

      //dd($item);
      $geojson->features[] = (object) [
        "type" => "Feature",
        "geometry" => (object) [
          "type" => "Point",
          "coordinates" => [$item->lng, $item->lat]
        ],
        "properties" => (object) [
          "title" => $item->shortName,
          "description" => $item->regionName,
          'marker-color' => '#f86767',
          'marker-size' => 'large',
          'marker-symbol' => $key,
        ]
      ];

    }
    $geojson->features[] =
     (object) [
        "type" => "Feature",
        "properties"=> (object)[],
        "geometry" => (object) [
          "type" => "LineString",
          "coordinates" => $line
        ]
    ];


    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");
    return (json_encode($geojson));

  }

  public function add($place){

    $user = empty($_GET['user']) ? Auth::user() : User::find($_GET['user']) ;
    if(!$user) $user = Auth::user();
    Auth::login($user);

    $place = Place::find($place);
    $user->tsp()->save($place);

  }

  public function remove($place){
    $user = empty($_GET['user']) ? Auth::user() : User::find($_GET['user']) ;
    if(!$user) $user = Auth::user();

    $place = Place::find($place);
    $user->tsp()->edge($place)->delete();

  }


}
