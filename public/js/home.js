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
  var map;
  var layer

  function initMap() {
    L.mapbox.accessToken = 'pk.eyJ1IjoidGhleXVycnkiLCJhIjoiY2lvOHRmMTZtMDA2c3Z5bHlicTNwZm9qaCJ9.TQBntaKZdYrhFkB2E7Zu7g';

    map = L.mapbox.map('main_map', 'mapbox.k8xv42t9');

    layer = L.mapbox.featureLayer()
      .loadURL("/placeapi?type=geojson")
      .on('ready', function(e) {

        var clusterGroup = new L.MarkerClusterGroup();
        e.target.eachLayer(function(layer) {
          clusterGroup.addLayer(layer);
        });
        map.addLayer(clusterGroup);
        map.fitBounds(clusterGroup.getBounds());
      });
  }

  function mapRefresh(){
    map.eachLayer(function (layer) {
      if(layer.feature)
        map.removeLayer(layer);
    });
    L.mapbox.featureLayer()
      .loadURL("/placeapi?type=geojson")
      .on('ready', function(e) {

        var clusterGroup = new L.MarkerClusterGroup();
        e.target.eachLayer(function(layer) {
          clusterGroup.addLayer(layer);
        });
        map.addLayer(clusterGroup);
        map.fitBounds(clusterGroup.getBounds());
      });
  }


  var polyline = {};

  $(document).on('click', '.focus-map', function (e) {
    e.preventDefault();
    e.stopPropagation()

    map.eachLayer(function (layer) {

      if(layer.feature || layer._featureGroup ){
        map.removeLayer(layer);
      }
      map.removeLayer(polyline);

    });

    var latlngs = [
      L.latLng($(e.target).data('lat'), $(e.target).data('lng')),
      L.latLng(home.lat,home.lng)

    ];
    polyline = L.polyline(latlngs, {color: 'red'}).addTo(map);

    map.fitBounds(polyline.getBounds());

  })


  /*
   * HANDLEBARS
   */
  var template
  $(document).ready(refreshPage).on('appRefresh',refreshPage)
  $(document).ready(function(){
    template = Handlebars.compile($("#places-template").html());
  });
  function refreshPage (){

    $.get('/placeapi?type=template', function (data) {
      data = JSON.parse(data);
      home = data.places[0];
      $('#places_body').html(template(data));
    });

    var recommendedTemplate = Handlebars.compile($("#recommended-template").html());

    $.get('/placeapi?type=template&filter=recommend', function (data) {
      data = JSON.parse(data);
      //console.log(data);
      $('#recommend_body').html(recommendedTemplate(data));
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

  $(document).on('click', '.recommended-add', function (e) {
    e.preventDefault();
    e.stopPropagation()

    $.ajax({
      url: '/placeapi/add/'+$(e.target).data('id'),
      type: 'POST',
      success: function (data) {
        data = JSON.parse(data);
        $(document).trigger('appRefresh');
      }
    });

  })






  // return app;
}(jQuery,appData));
