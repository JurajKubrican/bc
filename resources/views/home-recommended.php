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
    <td >
        {{#each followers}}
        <div class="follower-head" title="{{this}}"><span>{{this}}</span></div>
        {{/each }}
    </td>
    <td ><sapan class="glyphicon glyphicon-plus recommended-add" ></sapan></td>
    </tr>
  {{/each}}
  </table>
</script>
