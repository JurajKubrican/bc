var App = App||{}
App.Map = (function (map,App) {
  ('use strict');
  document.addEventListener("DOMContentLoaded", function(event) {
    loadScript('/js/mapbox.js',init);
  });

  function init(){

  }

  Map.startMap(id,lat,lon,data,opt){

    var map =  L.mapbox.map(id,(opt.tile||'mapbox.streets'))
        .setView([lat,lon], 10);

    return map
  }

  function loadScript(src, callback){
    var s,
        r,
        t;
    r = false;
    s = document.createElement('script');
    s.type = 'text/javascript';
    s.src = src;
    s.onload = s.onreadystatechange = function() {
      //console.log( this.readyState ); //uncomment this line to see which ready states are called.
      if ( !r && (!this.readyState || this.readyState == 'complete') )
      {
        r = true;
        callback();
      }
    };
    t = document.getElementsByTagName('script')[0];
    t.parentNode.insertBefore(s, t);
  }


}(App||{}));
