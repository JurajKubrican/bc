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
                      <div class="col-sm-3"><a href="#">{{$place['shortName']}}</a></div>
                      <div class="col-sm-3">{{$place['description']}}</div>
                      <div class="col-sm-3">${{$place['price']}} (${{$place['priceLow']}} - ${{$place['priceHigh']}})</div>
                      <div class="col-sm-1">
                        <form action="/place/{{ $place['id'] }}/" method="POST">
                          <input name="id" type="hidden" value="{{ $place['id'] }}"/>
                          <input name="_method" type="hidden" value="DELETE"/>
                          <input name="delete" value="X" type="submit"/>
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
