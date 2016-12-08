@extends('layouts.app')

@section('js')
  <script src='https://api.mapbox.com/mapbox.js/v2.4.0/mapbox.js'></script>
  <script src="js/settings.js" charset="utf-8"></script>
@endsection


@section('css')
  <link href='https://api.mapbox.com/mapbox.js/v2.4.0/mapbox.css' rel='stylesheet' />
  <link href='css/settings.css' rel='stylesheet' />
@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Home settings</div>
                <div class="panel-body">
                  <div id="map_home"></div>
                  <form class="form-inline" action="/settings" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                      <label for="name" class="col-md-4 control-label">Home</label>

                      <div class="col-md-6">
                        <input id="home-data" type="hidden" name="search-data" value="" />
                        <input id="home-place" class="form-control" name="place" type="text" value="{{$home->longName}}" data-provide="typeahead">
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-4">
                        <button type="submit" class="btn btn-primary">
                          Make my home
                        </button>
                      </div>
                    </div>

                  </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Home</div>
                <div class="panel-body">
                  <form id="password-change" class="form-inline" action="/settings" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">

                      <label class="col-md-4 control-label">Password
                        <input class="form-control" name="password" type="password" value="" >
                      </label>

                      <label class="col-md-4 control-label">Repeat password
                        <input class="form-control" name="password-confirm" type="password" value="" >
                      </label>

                      <div class="form-group">
                        <div class="col-md-6 col-md-offset-4">
                          <button type="submit" class="btn btn-primary">
                          Save
                          </button>
                        </div>
                      </div>

                    </form>
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>

<div id="data" data-home="{{$home}}"></div>
@endsection
