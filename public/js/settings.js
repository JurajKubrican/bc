(function ($,Bloodhound) {
  ('use strict');

  /*
  * R2R
  */
  $(document).ready(function() {

    initHomeForm();
    initMap();
    initValidation();
  });

  function makeGeoJSON(data){
     var geojson = {
      type: 'FeatureCollection',
      features: []
    };
    var features = [];
    console.log(
      data
    );
    data.forEach(function(item){
      if(typeof(item.lng)==='undefined' && typeof(item.lat)==='undefined')
      return;
      features.push({
        "type": "Feature",
        "geometry": {
          "type": "Point",
          "coordinates": [item.lng,item.lat]
        },
        "properties": {
          "title": item.longName,
        }
      })
    })
    geojson.features = features;

    return geojson;
  }

  function initMap(){
    L.mapbox.accessToken = 'pk.eyJ1IjoidGhleXVycnkiLCJhIjoiY2lvOHRmMTZtMDA2c3Z5bHlicTNwZm9qaCJ9.TQBntaKZdYrhFkB2E7Zu7g';
    var home =$('#data').data('home');
    var map =  L.mapbox.map('map_home','mapbox.streets',{
  	   center: [home.lat, home.lng],
  	    zoom: 7
    });

    var geojson = makeGeoJSON([home]);

    map.featureLayer.setGeoJSON(geojson);

  }

  function initHomeForm(){
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
  }


  /*
  * VALIDATION
  */
  function initValidation(){
    var root = $('#password-change');
    $('input[type=password]',root).on('input',function(){
      var pass = true;
      if( $('input[name=password]',root).val().length < 9 ){
        $('input[name=password]',root).addClass('invalid');
        pass = false;
      }else{
        $('input[name=password]',root).removeClass('invalid');
      }

      if( $('input[name=password-confirm]',root).val().length < 9 ||  $('input[name=password-confirm]',root).val()!== $('input[name=password]',root).val() ){
        $('input[name=password-confirm]',root).addClass('invalid');
        pass = false;
      }else{
        $('input[name=password-confirm]',root).removeClass('invalid');
      }


      if(pass)
      {
        $('button',root).removeAttr('disabled');
      }else{
        $('button',root).attr('disabled',true);
      }
    })

  }

}($, Bloodhound ));
