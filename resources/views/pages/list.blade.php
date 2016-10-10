@extends('layouts.layout')

@section('title')
 Title
@stop

@section('body')
  @foreach ($places as $place)
    <div class="col-md-6 portfolio-item">
        <a href="#">
            <img class="img-responsive" src="{{$place['img']}}" alt="">
        </a>
        <h3>
            <a href="#">{{$place['name']}}</a>
        </h3>
        <p>{{$place['description']}}</p>
    </div>
  @endforeach
@stop
