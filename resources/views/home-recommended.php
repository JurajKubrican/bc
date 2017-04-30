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
      <a class="person-link" href="/user/{{this.id}}">
          <div class="follower-head" title="{{this.name}}"></div>
          <div class="follower-letter">{{this.name}}</div>
        </a>
        {{/each }}
    </td>
    <td ><a class="glyphicon glyphicon-plus recommended-add" data-id="{{id}}" href="#" ></a></td>
    </tr>
  {{/each}}
  </table>
</script>
