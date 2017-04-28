<script id="places-template" type="text/x-handlebars-template">
  <table class="table">
    {{#each places}}
    <tr>
      <td ><a href="#" data-id="{{shortName}}" class="zoom-map">{{shortName}}</a></td>
      <td >{{regionName}}</td>
      <td >
        {{#each followers}}
        <a href="/user/{{this.id}}">
          <div class="follower-head" title="{{this.name}}"><span>{{this.name}}</span></div>
        </a>
        {{/each }}
      </td>
    </tr>
    {{/each}}
  </table>
</script>
