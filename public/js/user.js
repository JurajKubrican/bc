var app = (function($,appData) {
  ('use strict');

  $(document).ready(function() {
    initMap();
  });
  var gmap;
  var layer

  function initMap() {
    L.mapbox.accessToken = 'pk.eyJ1IjoidGhleXVycnkiLCJhIjoiY2lvOHRmMTZtMDA2c3Z5bHlicTNwZm9qaCJ9.TQBntaKZdYrhFkB2E7Zu7g';

    gmap = L.mapbox.map('main_map', 'mapbox.k8xv42t9');

    layer = L.mapbox.featureLayer()
      .loadURL("/placeapi?type=geojson&user=" + appData.user)
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
      .loadURL("/placeapi?type=geojson&user=" + appData.user)
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
  $(document).on('appRefresh',refreshPage);
  var template
  $(document).ready(function(){
    template = Handlebars.compile($("#places-template").html());
  })
  function refreshPage (){

    $.get('/placeapi?type=template&user=' + appData.user, function (data) {
      data = JSON.parse(data);
      console.log(data);
      $('#places_body').html(template(data));
    });

  }



  $(document).on('click', '.focus-map', function (e) {
    e.preventDefault();
    e.stopPropagation()

    console.log($(e.target).data('id'));
    console.log(layer);

  })


  // return app;
}(jQuery,appData||{}));
