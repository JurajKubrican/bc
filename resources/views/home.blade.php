@extends('layouts.app')

@section('css')
  <link href='https://api.mapbox.com/mapbox.js/v2.4.0/mapbox.css' rel='stylesheet' />
  <link href='https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/MarkerCluster.css' rel='stylesheet' />
  <link href='https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/MarkerCluster.Default.css' rel='stylesheet' />
  <link href='css/home.css' rel='stylesheet' />
@endsection

@section('js')

  <script src='https://api.mapbox.com/mapbox.js/v2.4.0/mapbox.js'></script>
  <script src='https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/leaflet.markercluster.js'></script>
  <script src="js/home.js" charset="utf-8"></script>
@endsection

@section('nav')
  @if (!Auth::guest())
    <form class="form-inline" action="/place" method="post">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input id="search-data" type="hidden" name="search-data" value="" />
      <input id="search-bar" class="form-control" name="place" type="text" data-provide="typeahead">
      <input class="form-control button" type="submit" value="Follow">
    </form>
  @endif
@endsection

@section('content')
<div class="container">
  <div id="main_map" data-map='{{$map}}'></div>
  <div class="row">
      <div class="col-md-8 col-md-offset-2">
          <div class="panel panel-default">
              <div class="panel-heading">Dashboard</div>

              <div class="panel-body">
                @foreach ($places as $place)
                  <div class="row">
                    <div class="col-sm-3"><a href="#">{{$place['shortName']}}</a></div>
                    <div class="col-sm-3">{{$place['description']}}</div>
                    <div class="col-sm-3">{{$place['price']}}€ ({{$place['priceLow']}}€ - {{$place['priceHigh']}}€)</div>
                    <div class="col-sm-1">
                      <form action="/place/{{ $place['id'] }}/" method="POST">
                        <input name="id" type="hidden" value="{{ $place['id'] }}"/>
                        <input name="_method" type="hidden" value="DELETE"/>
                        <label class="remove-button">
                          <span class="glyphicon glyphicon-remove"></span>
                          <input name="delete" class="hide" value="X" type="submit"/>
                        </label>
                      </form>
                    </div>
                  </div>
                @endforeach
              </div>
          </div>
      </div>
  </div>
</div>
@endsection
