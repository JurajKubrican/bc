@extends('layouts.app')

@section('css')
  <link href='https://api.mapbox.com/mapbox.js/v2.4.0/mapbox.css' rel='stylesheet' />
  <link href='https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/MarkerCluster.css' rel='stylesheet' />
  <link href='https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/MarkerCluster.Default.css' rel='stylesheet' />
  <link href='/css/home.css' rel='stylesheet' />
@endsection

@section('js')

  <script src='https://api.mapbox.com/mapbox.js/v2.4.0/mapbox.js'></script>
  <script src='https://api.tiles.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v0.4.0/leaflet.markercluster.js'></script>
  <script src="/js/place.js" charset="utf-8"></script>
  <script src="/js/handlebars-v4.0.5.js" charset="utf-8"></script>
@endsection

@section('nav')
  @if (!Auth::guest())
    <form class="form-inline" action="/place" method="post">
      <input type="hidden" name="_token" value="{{ csrf_token() }}">
      <input id="search-data" type="hidden" name="search-data" value="" />
      <input id="search-bar" class="form-control" name="place" type="text" data-provide="typeahead">
      <input style="margin-top: 6px;height: 30px;line-height: 17px;" class="form-control button" type="submit" value="Follow">
    </form>
    <a href="/tsp" class="button">TSP</a>
  @endif
@endsection

@section('content')
  @include('home-places')
  @include('home-recommended')
  {{--<script>const appData = {--}}
      {{--user:"{{$user}}"--}}
    {{--}</script>--}}
  <div class="container">
    <div id="main_map"></div>
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
          <div class="panel-heading">Dashboard</div>
          <div id="places_body" class="panel">

          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
          <div class="panel-heading">Recommendations</div>

          <div id="recommend_body" class="panel">
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
