
<script id="places-template" type="text/x-handlebars-template">
  <div class="entry">
    <h1>{{title}}</h1>
    <div class="body">
      {{body}}
    </div>
  </div>
  {{#each places}}
  <div class="row">
    <div class="col-sm-3"><a href="#">{{shortName}}</a></div>
    <div class="col-sm-3">{{regionName}}</div>
    <div class="col-sm-3">PRICE</div>
    <div class="col-sm-1">
      X
    </div>
  </div>
   {{/each}}
</script>

<script>
$(document).ready(function(){
  var template = Handlebars.compile($("#places-template").html());
  $.get('/placeapi?type=template',function(data){
    data = JSON.parse(data);
    console.log(data);
    $('#places_body').html(template(data));
  });
})
</script>
