var app = (function($) {
  ('use strict');

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
      .loadURL("/placeapi?type=geojson")
      .on('ready', function(e) {

        var clusterGroup = new L.MarkerClusterGroup();
        e.target.eachLayer(function(layer) {
          clusterGroup.addLayer(layer);
        });
        gmap.addLayer(clusterGroup);
        gmap.fitBounds(clusterGroup.getBounds());
      });
  }

  function mapRefresh(){
    gmap.eachLayer(function (layer) {
      if(layer.feature)
        gmap.removeLayer(layer);
    });
    L.mapbox.featureLayer()
      .loadURL("/placeapi?type=geojson")
      .on('ready', function(e) {

        var clusterGroup = new L.MarkerClusterGroup();
        e.target.eachLayer(function(layer) {
          clusterGroup.addLayer(layer);
        });
        gmap.addLayer(clusterGroup);
        gmap.fitBounds(clusterGroup.getBounds());
      });
  }

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

    $.get('/placeapi?type=template', function (data) {
      data = JSON.parse(data);
      console.log(data);
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


  $(document).on('click', '.focus-map', function (e) {
    e.preventDefault();
    e.stopPropagation()

    console.log($(e.target).data('id'));
    console.log(layer);

  })


  // return app;
}(jQuery));
