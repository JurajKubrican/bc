<?php
use App\Place;
use App\Helper
namespace App;

class r2rSearch {
  private $aVehicles;
  private $aPlaces;
  private $aRoutes;
  private $cacheDir = __DIR__.'/cache/';


  public function __construct($from, $to, $options=['noCar'=>true,'noRideshare'=>true, 'noTowncar'=>true,'currencyCode'=>'EUR']){
    $data = $this->cachedRequest($from,$to,$options);

    $this->aVehicles = $data->vehicles;
    $this->aPlaces = $data->places;
    $this->aRoutes = $data->routes;
  }

  public function getRoutes(){
    $result = [];
    foreach ($this->aRoutes as $route){
      if(!isset($route->indicativePrices)){
        $route->indicativePrices = '?';
        $routePriceLow = 0;
        $routePriceHigh = 0;
        $routePrice = 0;
      }else{
        $routePriceLow = isset($route->indicativePrices[0]->priceLow) ? $route->indicativePrices[0]->priceLow : $route->indicativePrices[0]->price;
        $routePriceHigh = isset($route->indicativePrices[0]->priceHigh) ? $route->indicativePrices[0]->priceHigh : $route->indicativePrices[0]->price;
        $routePrice = $route->indicativePrices[0]->price;
      }

      $result[] = (object)[
                'price' => $routePrice,
                'priceLow' => $routePriceLow,
                'priceHigh' => $routePriceHigh,
                'segments' => $this->parseSegments($route->segments),
                ];
    }
    return $result;
  }

  private function cachedRequest($from, $to,$options){
    $query_data = [
      'oName' => $from->canonicalName,
      'dName' => $to->canonicalName,
      'key'=>'9kvuKwqZ',
    ];
    $query_data = array_merge($query_data,$options);
    $cache = $from->cache()->edge($to);
    //dd($cache->time);
    if(!$cache)
      $cache = $from->cache()->save($to);

    if( !($cache->file && $cache->time < time() + 3600 && $data = file_get_contents($cache->file))){
      $data = $this->request($query_data);
      $fname = $this->remove_accents($this->cacheDir.$from->canonicalName . '-' . $to->canonicalName);
      file_put_contents($fname,$data);
      $cache->file = $fname;
      $cache->time = time();
      $cache->save();
    }
    return json_decode($data);
  }

  private function request($query_data){

    $aQuery = '';
    foreach($query_data as $key => $val){
      $aQuery[] = urlencode($key).'='.urlencode($val);
    }
    $sQuery = 'http://free.rome2rio.com/api/1.4/json/Search?'.implode('&',$aQuery);
    $s = curl_init();
    curl_setopt($s,CURLOPT_URL,$sQuery);
    curl_setopt($s,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($s,CURLOPT_HEADER,true);
    $response = curl_exec($s);
    $header_size = curl_getinfo($s, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    return $body;
  }

  private function parseSegments($segments){
    $result = [];
    foreach($segments as $segment){
      $place = $this->aPlaces[$segment->arrPlace];
      if(isset($place->kind) && $place->kind == "airport"){
          $place->canonicalName = $place->code . '-' . $place->kind;
          $result[] = $place;
      }

      $place = $this->aPlaces[$segment->depPlace];
      if(isset($place->kind) && $place->kind == "airport"){
          $place->canonicalName = $place->code . '-' . $place->kind;
          $result[] = $place;
      }
    }
    return $result;
  }


  private function parseIndicativePrices($indicative){
    return;
  }



}
