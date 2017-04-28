(function($) {
  ('use strict');
  $(document).ready(function() {
    initMap();
  });

  var gmap;
  var layer

  function initMap() {
    L.mapbox.accessToken = 'pk.eyJ1IjoidGhleXVycnkiLCJhIjoiY2lvOHRmMTZtMDA2c3Z5bHlicTNwZm9qaCJ9.TQBntaKZdYrhFkB2E7Zu7g';

    gmap = L.mapbox.map('main_map', 'mapbox.k8xv42t9');

    L.mapbox.featureLayer()
      .loadURL("/placeapi?type=geojson&filter=suggested")
      .on('ready', function(e) {

        var clusterGroup = new L.MarkerClusterGroup();
        e.target.eachLayer(function(layer) {
          clusterGroup.addLayer(layer);
          $(document).on('click','.zoom-map[data-id="' +layer.feature.properties.title + '"]',function(){
            gmap.setView(layer.getLatLng(),8);
          })
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
    $.get('/placeapi?type=template&filter=suggested', function (data) {
      data = JSON.parse(data);
      console.log(data);
      $('#suggested_body').html(template(data));
    });

  }



}(jQuery));
