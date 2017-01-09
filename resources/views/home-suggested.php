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
  <div class="col-sm-1 delete" data-id="">
  X
  </div>
  </div>
  {{/each}}
</script>

<script>
  $(document).on('ready appRefresh', function () {
    var template = Handlebars.compile($("#places-template").html());
    $.get('/placeapi?type=template&filter=suggested', function (data) {
      data = JSON.parse(data);
      console.log(data);
      $('#suggested_body').html(template(data));
    });


    $('.delete').click(function () {
      console.log('DELETE' + $(this).data('id'));
      $.get('/placeapi?action=delete&id=' + $(this).data('id'), function (data) {
        data = JSON.parse(data);
        $(document).trigger('appRefresh');
      });
    });
  })
</script>
