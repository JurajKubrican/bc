<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Place;
use App\User;
use App\r2rSearch;
use Illuminate\Support\Facades\Auth;

class PlaceController extends Controller {

  public function store(Request $request) {
    $data = json_decode($request->all()['search-data']);

    //validation
    if (empty($data->canonicalName)) {
      return redirect('/');
    }

    $place = Place::findOrCreate($data);

    $user = Auth::user();
    //link to user
    if (!$user->follows()->get()->where('canonicalName', $data->canonicalName)->first()) {
      $user->follows()->save($place);
    }

    //link to home
    //TODO: IS this needed?
    $home = $user->home()->first();
    if (!$home->routes()->get()->where('canonicalName', $data->canonicalName)->first()) {
      $home->routes()->save($place);
    }

    return redirect('/');
  }

  public function index() {
    return redirect('/');
  }

//TODO:handle API
  public function destroy($id) {
    $user = Auth::user();
    $place = $user->follows()->get()->find($id);
    if ($place) {
      $user->follows()->edge($place)->delete();
    } else {
      //TODO:it does not follows
    }
    return redirect('/');
  }

  /**
   * crawler entry point
   */
  private function recountAll(){
    $allPlaces = Place::all();
    foreach ($allPlaces as $place){
      $place->followerCount = $place->followers()->count();
      $place->save();
    }
  }

  private function removeOld(){
    $allPlaces = Place::all();
    foreach ($allPlaces as $place){
      if($place->followers()->count() === 0){
        $place->delete();
      }
//      foreach($place->routes()->get() as $dest ){
//        $route = $place->routes()->edge($dest);
//        unset($route->history) ;
//        $route->save();
//      }
    }

  }

  private function enqueueAll() {

    $allUsers = User::all();
    foreach ($allUsers as $user) {
      $home = $user->home()->first();
      if (!$home)
        continue;
      foreach ($user->follows()->get() as $dest) {
        if (!$home->queue()->edge($dest)) {
          $home->queue()->save($dest);
        }
      }
    }
  }

  private function fetchMissing($place,$dest){
    $search = new r2rSearch($place, $dest);
    if (null === $routesEdge = $place->routes()->edge($dest)) {
      $routesEdge = $place->routes()->save($dest);
    }

    $routesEdge->minPrice = 99999999;
    $aEdgeData = [];
    foreach ($search->getRoutes() as $route) {


      $edgeData = (object) [];
      $edgeData->priceLow = $route->priceLow;
      $edgeData->priceHigh = $route->priceHigh;
      $edgeData->price = $route->price;
      $edgeData->typeName = $route->typeName;

      $routesEdge->minPrice = min($routesEdge->minPrice, $route->priceLow);

      $aEdgeData[] = $edgeData;
    }
    $routesEdge->routes = json_encode($aEdgeData);


    $routesEdge->save();

  }

  public function crawl() {
    $this->enqueueAll();
    $this->recountAll();



    $allPlaces = Place::all();
    foreach ($allPlaces as $place) {
      $allQueued = $place->queue()->get();
      if (!$allQueued->count())
        continue;
      foreach ($allQueued as $dest) {
        //$place->queue()->edge($dest);
        $this->fetchMissing($place, $dest);
//        if ($place->queue()->edge($dest)) {
//          $place->queue()->save($dest);
//        }
      }
    }

    $this->removeOld();
  }

  public function apiGet(Request $request) {

    $atts = $request->all();
    $atts += [
      'filter' => 'all',
      // 'action' => 'get',
    ];



    switch ($atts['filter']) {
      case 'recommend':
        $data = $this->getRecommendedPlaces($atts);
        break;
      case 'suggested':
        $data = $this->getSuggestedPlaces();
        break;
      case 'all':
      default:
        $data = $this->getAllPlaces($atts);
    }
    switch (strtolower($request->type)) {
      case 'template':
        $result = $this->wrapTemplate($data);
        break;
      case 'geojson':
        $result = $this->wrapGeoJSON($data);
        break;
    }
    die($result);
  }

  private function wrapGeoJSON($data, $options = null) {

    $geojson = (object) [
      'type' => 'FeatureCollection',
      'features' => []
    ];

    foreach ($data as $item) {
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
          'marker-symbol' => $item->symbol,
        ]
      ];
    }
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST');
    header("Access-Control-Allow-Headers: X-Requested-With");
    return (json_encode($geojson));

  }

  private function wrapTemplate($data) {
    $result = (object) [
      'places' => $data,
    ];

    return json_encode($result);
  }

  private function reduceFollowers($prev,$next){
//    dd(Auth::user() && Auth::user()->id === $next['id']);
    if(!(Auth::user() && Auth::user()->id === $next['id'] ))
    $prev [] = (object)[
      "id"=>$next['id'],
      "name"=>$next['name'],
    ];
    return $prev;
  }

  private function getAllPlaces($request) {
    $user = empty($request['user']) ? Auth::user() : User::find($request['user']) ;
    if(!$user) $user = Auth::user();
    $places = $user->follows()->get();
    $home = $user->home()->first();
    $data[] = (object) [
      'id' => $home->id,
      'shortName' => $home->shortName,
      'regionName' => $home->regionName,
      'lat' => $home->lat,
      'lng' => $home->lng,
      'symbol' => 'building',
    ];

    foreach ($places as $key => $place) {
      $route = $home->routes()->edge($place);
      if (!$route)
        continue;

      $followers = array_reduce($place->followers()->get()->toArray(),[$this,"reduceFollowers"],[]);

      if(null == $route->minPrice){
        //dd($user->home()->first()->canonicalName,$place->canonicalName);
        $this->fetchMissing($user->home()->first(),$place);
      }


      $data[] = (object) [
        'id' => $place->id,
        'shortName' => $place->shortName,
        'regionName' => $place->regionName,
        'lat' => $place->lat,
        'lng' => $place->lng,
        'symbol' => '',
        'price' => $route->minPrice,
        'followers' => $followers,
        'routes' => json_decode($route->routes),
      ];
    }
    return $data;

  }

  private function recommendReduce($prev,$new){
    $temp = $new->data;
    $temp->count = $new->count;
    $prev[] = $temp;
    return $prev;
  }

  private function recommendSort($a, $b){
    if ($a->count == $b->count) {
      return 0;
    }
    return ($a->count > $b->count) ? -1 : 1;
  }

  private function getIDs($prev,$next){
    $prev [] = $next['id'];
    return $prev;
  }

  private function recommendedFollowers($prev, $next){
    $user = empty($request['user']) ? Auth::user() : User::find($request['user']) ;
    if(!$user) $user = Auth::user();
    $place = Place::find($next);
    $followers = $place->followers()->get();
    foreach ($followers as $follower){
      if($follower->id === $user->id)
        continue;

      if (!in_array($follower->id, $prev))
      {
        $prev[] = $follower->id;
      }
    }
    return $prev;

  }


  private function getRecommendedPlaces($request){
    $user = empty($request['user']) ? Auth::user() : User::find($request['user']) ;
    if(!$user) $user = Auth::user();


    $places = $user->follows()->get();
    $recommended = [];

    $placeIDs = array_reduce((array)$places->toArray(),[$this,'getIDs'],[]);

    $followers = array_reduce($placeIDs,[$this,'recommendedFollowers'],[]);


    foreach($followers as $followerId) {
      $follower = User::find($followerId);
      $followed = $follower->follows()->get();

      foreach ($followed as $val) {
        if (in_array($val->id, $placeIDs))
          continue;

        if (isset($recommended[$val->id])) {
          $recommended[$val->id]->count++;
        } else {
          $followers = array_reduce($val->followers()->get()->toArray(), [$this, "reduceFollowers"], []);

          $recommended[$val->id] = (object)[
            'count' => 1,
            'data' => (object)[
              'id' => $val->id,
              'shortName' => $val->shortName,
              'regionName' => $val->regionName ? $val->regionName : '',
              'lat' => $val->lat,
              'lng' => $val->lng,
              'symbol' => '',
              'followers' => $followers,

            ],
          ];

        }


      }
    }
    usort($recommended,[$this,'recommendSort']);
    //dd($recommended);
    $recommended = array_reduce($recommended,[$this,'recommendReduce'],[]);
    return $recommended;

  }

  private function getSuggestedPlaces(){

    $places = Place::orderBy('followerCount', 'DESC')->take(30)->get();

    foreach ($places as $key => $place) {
      $data[] = (object) [
        'id' => $place->id,
        'shortName' => $place->shortName,
        'regionName' => $place->regionName ?  $place->regionName : ' ',
        'lat' => $place->lat,
        'lng' => $place->lng,
        'symbol' => '',
      ];
    }
    return $data;

  }

  public function apiDelete($placeId) {
    $user = Auth::user();
    $place = Place::find($placeId);
    $user->deleteFollows($place);
    die(json_encode(['error' => 0]));
  }



  public function apiAdd($placeId) {
    $user = Auth::user();
    $place = Place::find($placeId);
    $user->follows()->save($place);
    die(json_encode(['error' => 0]));
  }

}
