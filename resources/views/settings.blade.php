@extends('layouts.app')

@section('js')
  <script src="js/map.js" charset="utf-8"></script>
  <script src="js/settings.js" charset="utf-8"></script>
@endsection


@section('nav')

@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Settings</div>
                <div class="panel-body">
                  <form class="form-inline" action="/settings" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                      <label for="name" class="col-md-4 control-label">Home</label>

                      <div class="col-md-6">
                        <input id="home-data" type="hidden" name="search-data" value="" />
                        <input id="home-place" class="form-control" name="place" type="text" data-provide="typeahead">
                      </div>
                    </div>
                    <div class="form-group">
                      <div class="col-md-6 col-md-offset-4">
                        <button type="submit" class="btn btn-primary">
                          Register
                        </button>
                      </div>
                    </div>

                  </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
