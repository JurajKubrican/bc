var app = (function($,appData) {
  ('use strict');

  var home = {};

  $(document).ready(function() {
    initSearchBar();
    initMap();
  });
  $(document).on('appRefresh',mapRefresh);


  function initSearchBar() {
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
      $('#search-bar').typeahead({
        minLength: 1,
          hint:0
          //TODO: placeholder
      }, {
        name: 'best-pictures',
        displayKey: function(countries) {
          return countries.longName;
        },
        source: r2rAutocomplete
      }).on('typeahead:selected', function(event, data) {
        //console.log(data);
        $('#search-data').val(JSON.stringify(data));
      });
    })
  }
  var gmap;
  var layer

  function initMap() {
    L.mapbox.accessToken = 'pk.eyJ1IjoidGhleXVycnkiLCJhIjoiY2lvOHRmMTZtMDA2c3Z5bHlicTNwZm9qaCJ9.TQBntaKZdYrhFkB2E7Zu7g';

    gmap = L.mapbox.map('main_map', 'mapbox.k8xv42t9');

    layer = L.mapbox.featureLayer()
      .loadURL("/tsp/solve?type=geojson&user="+appData.user).addTo(gmap)
      .on('ready', function(e) {
        var group = new L.FeatureGroup();
        e.target.eachLayer(function(layer) {
          group.addLayer(layer);

        });
        gmap.fitBounds(group.getBounds())
        refreshPath();
      });


  }

  function mapRefresh(){

    layer.loadURL("/tsp/solve?type=geojson&user="+appData.user);



    //gmap.fitBounds(layer.getBounds());
  }


   var polyline = {};
  //
  // $(document).on('click', '.focus-map', function (e) {
  //   e.preventDefault();
  //   e.stopPropagation()
  //
  //   gmap.eachLayer(function (layer) {
  //
  //     if(layer.feature || layer._featureGroup ){
  //       gmap.removeLayer(layer);
  //     }
  //     gmap.removeLayer(polyline);
  //
  //   });
  //
  //   var latlngs = [
  //     L.latLng($(e.target).data('lat'), $(e.target).data('lng')),
  //     L.latLng(home.lat,home.lng)
  //
  //   ];
  //    polyline = L.polyline(latlngs, {color: 'red'}).addTo(gmap);
  //
  //   gmap.fitBounds(polyline.getBounds());
  //
  // })


  /*
   * HANDLEBARS
   */
  $(document).ready(refreshPage);
  $(document).on('appRefresh',refreshPage)
  var template
  $(document).ready(function(){
    template = Handlebars.compile($("#places-template").html());
  })
  function refreshPage (){

    $.get('/placeapi?type=template&user='+appData.user, function (data) {
      data = JSON.parse(data);
      home = data.places[0];
      $('#places_body').html(template(data));
    });

  }

  function refreshPath(){

    var template = Handlebars.compile($("#tsp-template").html());

    $.get('/tsp/solve?type=template&user='+appData.user, function (data) {
      data = JSON.parse(data);
      home = data.places[0];
      $('#path_body').html(template(data));
    });

  }

  $(document).on('click', '.delete', function (e) {
    e.preventDefault();
    e.stopPropagation();
    $.ajax({
      url: '/placeapi/' + $(this).data('id'),
      type: 'POST',
      success: function (data) {
        data = JSON.parse(data);
        //TODO error handling
        $(document).trigger('appRefresh');
      }
    });
  });


  $(document).on('click','.tsp-run',function(e){
    mapRefresh();
  })

  $(document).on('click','.tsp-add',function(e){

    e.preventDefault();
    e.stopPropagation()

    $.ajax({
      url: '/tsp/' + ($(e.target).hasClass('glyphicon-plus')? 'add' : 'remove') + '/' + $(e.target).data('id')+'?user='+appData.user,
      type: 'POST',
      success: function (data) {
        $(document).trigger('appRefresh');
      }
    });

  })



  // return app;
}(jQuery,appData));
