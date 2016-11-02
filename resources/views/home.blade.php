@extends('layouts.app')

@section('nav')

@endsection

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>

                <div class="panel-body">
                  @foreach ($places as $place)
                    <div class="row">
                        <h3>
                            <a href="#">{{$place['shortName']}}</a>
                        </h3>
                        <p>{{$place['description']}}</p>
                    </div>
                  @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
