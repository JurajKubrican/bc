<script id="places-template" type="text/x-handlebars-template">
  <table class="table">
    {{#each places}}
    <tr>
      <td ><a href="#" data-id="{{shortName}}" class="zoom-map">{{shortName}}</a></td>
      <td >{{regionName}}</td>
      <td >
        {{#each followers}}
        <a class="person-link" href="/user/{{this.id}}">
          <div class="follower-head" title="{{this.name}}"></div>
          <div class="follower-letter">{{this.name}}</div>
        </a>
        {{/each }}
      </td>
    </tr>
    {{/each}}
  </table>
</script>
