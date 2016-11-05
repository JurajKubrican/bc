@extends('layouts.app')

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
                    <input id="home-data" type="hidden" name="search-data" value="" />
                    <input id="home-place" class="form-control" name="place" type="text" data-provide="typeahead">
                    <input class="form-control button" type="submit">
                  </form>
                  <script type="text/javascript">
                  $(document).ready(function() {
                    var r2rAutocomplete = new Bloodhound({
                      datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
                      queryTokenizer: Bloodhound.tokenizers.whitespace,
                      remote: {
                        url: 'https://www.rome2rio.com/api/1.2/json/autocomplete?query=%QUERY&resultType=r2r',
                        wildcard: '%QUERY',
                        filter: function(response) {
                          return response.places;
                        }
                      }
                    });
                    $('#home-place').typeahead({ hint: true,
                      highlight: true,
                      minLength: 1
                    }, {
                      name: 'best-pictures',
                      displayKey: function(countries) {
                        return countries.longName;
                      },
                      source: r2rAutocomplete
                    }).on('typeahead:selected', function(event, data){
                      $('#home-data').val(JSON.stringify(data));
                    });
                  })
                  </script>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
