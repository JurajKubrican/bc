(function($) {
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
        minLength: 1
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

  function refreshPage (){
    var template = Handlebars.compile($("#places-template").html());
    $.get('/placeapi?type=template', function (data) {
      data = JSON.parse(data);
      $('#places_body').html(template(data));
    });

    $(document).on('click', '.delete', function (e) {
      e.preventDefault();
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

    var recommendedTemplate = Handlebars.compile($("#recommended-template").html());

    $.get('/placeapi?type=template&filter=recommend', function (data) {
      data = JSON.parse(data);
      //console.log(data);
      $('#recommend_body').html(recommendedTemplate(data));
    });
  }



}(jQuery));
