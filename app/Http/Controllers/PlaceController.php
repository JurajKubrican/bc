<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Place;
use App\User;
use App\r2rSearch;
use Illuminate\Support\Facades\Auth;
use GraphAware\Neo4j\Client\ClientBuilder;

class PlaceController extends Controller {

  public function store(Request $request) {
    $data = json_decode($request->all()['search-data']);

    //validation
    if (empty($data->canonicalName)) {
      return redirect('/');
    }
    $place = Place::findOrCreate($data);
    $user = Auth::user();

    if($user->home()->first() == $place){
      return redirect('/');
    }


    //link to user
    if (!$user->follows()->get()->where('canonicalName', $data->canonicalName)->first()) {
      $user->follows()->save($place);
    }

    //link to home
    $home = $user->home()->first();
    if (!$home->routes()->get()->where('canonicalName', $data->canonicalName)->first()) {
      $home->routes()->save($place);
      $this->fetchMissing($home,$place);
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


  public static function enqueueAllTSP() {

    $user = Auth::user();
    $places = $user->follows()->get();
    foreach ($places as $place1) {
      foreach($places as $place2){
          if($place1===$place2){
            continue;
          }

        if (!$place1->queue()->edge($place2)) {
          $place1->queue()->save($place2);
        }
      }
    }
  }


  private function enqueueAll() {

    $allUsers = User::all();
    foreach ($allUsers as $user) {
      $home = $user->home()->first();
      if (!$home){
        continue;
      }

      foreach ($user->follows()->get() as $dest) {
       // dump([$home,$dest]);
        if($home == $dest){
          $user->deleteFollows($dest);
          continue;
        }
        if (!$home->queue()->edge($dest)) {
          $home->queue()->save($dest);
        }
      }
    }
  }

  public static function fetchMissing($place,$dest){
    $search = new r2rSearch($place, $dest);
    //dump(['fetching',$place->shortName,$dest->shortName]);
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

      if($routesEdge->minPrice > $route->priceLow && $route->priceLow != 0){
        $routesEdge->minPrice = $route->priceLow;
      }


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
      case 'new':
        $data = $this->getAllPlacesNew($atts);
        break;

      case 'export':
        $data = $this->getAllPlacesExport($atts);
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
    if(!(Auth::user() && Auth::user()->id === $next['id'] ))
    $prev [] = (object)[
      "id"=>$next['id'],
      "name"=>empty($next['name']) ? $next['email'] : $next['name'],
    ];
    return $prev;
  }

  private function getAllPlaces($request) {

    $user = empty($request['user']) ? Auth::user() : User::find($request['user']) ;
    if(!$user) $user = Auth::user();
    return [];
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

      //dump([$home,$place]);
      //dump($home->routes()->edge($place));
      $route = $home->routes()->edge($place);
      if(null == $route)
        $this->fetchMissing($user->home()->first(),$place);

      $followers = array_reduce($place->followers()->take(5)->get()->toArray(),[$this,"reduceFollowers"],[]);


      if($route == null)
        continue;

      $tsp = $user->tsp()->edge($place) ? 1 : '';

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
        'tsp' => $tsp,
      ];
    }

    $time = time() - $time;
    die($time);
    return $data;

}


  private function getAllPlacesNew(){

    $user = empty($request['user']) ? Auth::user() : User::find($request['user']) ;
    if(!$user) $user = Auth::user();

    $userId = $user->id;

    $client = ClientBuilder::create()
      ->addConnection('bolt', 'bolt://neo4j:batlefield@localhost:7687')
      ->build();
    $query = "match (u:AppUser)-[:FOLLOWS]->(p) where id(u)=$userId
      match (u)-[:HOME]->(h)
      match (h)-[r:ROUTES]->(p)
      match (f:AppUser)-[:FOLLOWS]->(p)
      optional match (u)-[t:TSP]->(p)
      WHERE f <> u
      return r as route,p as place,ID(p)as id,f.name as follower,t as tsp;";
    $result = $client->run($query);

    $home = $user->home()->first();
    $data[] = (object) [
      'id' => $home->id,
      'shortName' => $home->shortName,
      'regionName' => $home->regionName,
      'lat' => $home->lat,
      'lng' => $home->lng,
      'symbol' => 'building',
    ];

    foreach ($result->getRecords() as $record) {



      $id = $record->value('id');
      $place = $record->value('place');
      $route = $record->value('route');
      $tsp = $record->value('tsp') ? 1 : 0;
      $follower =  $record->value('follower') ;


      if(empty($data[$id])){

        $data[$id] = (object) [
          'id' => $id,
          'shortName' => $place->value('shortName'),
          'regionName' => $place->hasValue('regionName') ? $place->value('regionName') : '',
          'lat' => $place->value('lat'),
          'lng' => $place->value('lng'),
          'symbol' => '',
          'price' => $route->hasValue('minPrice') ? $route->value('minPrice') : 999999999,
          'followers' => [$follower],
          'routes' => json_decode($route->hasValue('routes') ? $route->value('routes') : '[]'),
          'tsp' => $tsp,
        ];

      }else{

        $data[$id]->folowers[] = $follower;
      }
    }
    $data =  array_values($data);
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
    $user = Auth::user();
    $place = Place::find($next);
    $followers = $place->followers()->take(5)->get();
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
          $followers = array_reduce($val->followers()->take(5)->get()->toArray(), [$this, "reduceFollowers"], []);

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


  private function generateCSV($data,$filename){

    $file = '';

    foreach ($data as $line)
    {
      $file .=implode("\t",$line)."\n";
    }

    file_put_contents($filename,$file);
  }

  private function getAllPlacesExport(){

    $client = ClientBuilder::create()
      ->addConnection('bolt', 'bolt://neo4j:batlefield@localhost:7687')
      ->build();
    $query = "match (u:AppUser) return u;";
    $result = $client->run($query);
    $data = [];
    foreach ($result->getRecords() as $record) {
      $values = $record->values();
      $row = $values[0]->values();


      $row['id'] = $values[0]->identity();

      unset($row['tspCache']);
      unset($row['remember_token']);

      $data[] = $row;
      //echo implode("\t",$row)."\n";
    }
    $this->generateCSV($data,__DIR__.'/users.csv');
      echo "\n\n================================\n\n";

    $query = "match (u:AppPlace) return u,id(u);";
    $result = $client->run($query);

    $data = [];
    foreach ($result->getRecords() as $record) {
      $values = $record->values();
      $row = $values[0]->values();

      $row[0] = $values[0]->identity();
      $row = array_reverse($row);

      //echo implode("\t",$row)."\n";
      $data[] = $row;
    }
    $this->generateCSV($data,__DIR__.'/places.csv');

    echo "\n\n================================\nFOLLOWS\n";

    $query = "match (u)-[r:FOLLOWS]->(p) return id(u) as user, id(p) as place";
    $result = $client->run($query);

    $data = [];
    foreach ($result->getRecords() as $record) {
      $row = $record->values();
      $data[] = $row;
      //echo implode("\t",$record->values())."\n";
    }
    $this->generateCSV($data,__DIR__.'/follows.csv');

    echo "\n\n================================\nROUTES\n";

    $query = "match (u)-[r:ROUTES]->(p) return id(u) as user, id(p) as place , r";
    $result = $client->run($query);

    $data = [];
    foreach ($result->getRecords() as $record) {
      $row = [];

      $row[] = $record->value('user');
      $row[] = $record->value('place');


      $row += (array)$record->values()[2]->values();

      $data[] = $row;

      //echo implode("\t",$row)."\n";
    }
    $this->generateCSV($data,__DIR__.'/routes.csv');


    echo "\n\n================================\nHOME\n";

    $query = "match (u)-[r:HOME]->(p) return id(u) as user, id(p) as place;";
    $result = $client->run($query);

    $data = [];
    foreach ($result->getRecords() as $record) {

      $row = $record->values();
      $data[] = $row;
      //echo implode("\t",$record->values())."\n";
    }
    $this->generateCSV($data,__DIR__.'/home.csv');

    echo "\n\n================================\nTSP\n";

    $query = "match (u)-[r:TSP]->(p) return id(u) as user, id(p) as place;";
    $result = $client->run($query);

    $data = [];
    foreach ($result->getRecords() as $record) {

      $row = $record->values();
      $data[] = $row;
      //echo implode("\t",$record->values())."\n";
    }
    $this->generateCSV($data,__DIR__.'/tsp.csv');
    die;
  }

  private function getSuggestedPlaces(){

    $places = Place::orderBy('followerCount', 'DESC')->take(30)->get();

    foreach ($places as $key => $place) {

      $followers = array_reduce($place->followers()->take(5)->get()->toArray(), [$this, "reduceFollowers"], []);

      $data[] = (object) [
        'id' => $place->id,
        'shortName' => $place->shortName,
        'regionName' => $place->regionName ?  $place->regionName : ' ',
        'lat' => $place->lat,
        'lng' => $place->lng,
        'symbol' => '',
        'followers' => $followers,
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
    if($user->home()->first() != $place);
    $user->follows()->save($place);
    die(json_encode(['error' => 0]));
  }

}
