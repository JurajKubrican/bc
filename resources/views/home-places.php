
<script id="places-template" type="text/x-handlebars-template">
  <div class="entry">
    <h1>{{title}}</h1>
    <div class="body">
      {{body}}
    </div>
  </div>
  {{#each places}}
    {{#if symbol}}
    {{else}}
    <div class="row">
      <div class="col-sm-3"><a href="#">{{shortName}}</a></div>
      <div class="col-sm-3">{{regionName}}</div>
      <div class="col-sm-3">{{price}}</div>
      <div class="col-sm-1"><a class="delete glyphicon glyphicon-remove" data-id="{{id}}" href="#"></a></div>
    </div>
    {{/if}}
  {{/each}}
</script>

<script>
(function(){
  ('use strict');
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
  }
}())


</script>
