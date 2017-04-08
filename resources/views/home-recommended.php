<script id="recommended-template" type="text/x-handlebars-template">
  <div class="entry">
    <h1>{{title}}</h1>
    <div class="body">
      {{body}}
    </div>
  </div>
  <table class="table">
  {{#each places}}
  <tr>
    <td ><a href="#">{{shortName}}</a></td>
    <td >{{regionName}}</td>
    <td >{{count}}</td>
    </tr>
  {{/each}}
  </table>
</script>
