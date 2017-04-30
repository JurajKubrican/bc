var app = (function($,appData) {
  ('use strict');

  var home = {};
  var busy = false;

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
        $('.glyphicon-refresh').each(function(){
          $(this).addClass( $(this).data('class') ).removeClass('glyphicon-refresh');
        })
      });


  }

  function mapRefresh(){
    layer.loadURL("/tsp/solve?type=geojson&user="+appData.user);
  }


   var polyline = {};


  /*
   * HANDLEBARS
   */
  $(document).ready(refreshPage);
  $(document).on('appRefresh',refreshPage)
  function refreshPage (){
    var template = Handlebars.compile($("#places-template").html());

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
      $('.loader').removeClass('loader');
      busy = false;
    });

  }


  $(document).on('click','.tsp-add',function(e){
    e.preventDefault();
    e.stopPropagation();

    $.ajax({
      url: '/tsp/' + ($(e.target).hasClass('glyphicon-plus')? 'add' : 'remove') + '/' + $(e.target).data('id')+'?user='+appData.user,
      type: 'POST',
      success: function (data) {
        mapRefresh();
      }
    });

    $(e.target).data('class',($(e.target).hasClass('glyphicon-plus') ? 'glyphicon-ok' : 'glyphicon-plus'))
      .removeClass('glyphicon-plus').removeClass('glyphicon-ok').addClass('glyphicon-refresh');

  })



  // return app;
}(jQuery,appData));
