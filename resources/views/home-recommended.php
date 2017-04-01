<script id="recommended-template" type="text/x-handlebars-template">
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
  </div>
  {{/each}}
</script>
