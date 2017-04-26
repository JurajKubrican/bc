
<script id="tsp-template"  type="text/x-handlebars-template">
  <table class="table table-striped">
    {{#each places}}
    {{#if symbol}}
    {{else}}
    <tr>
      <td>
        <div>
          <div class="col-sm-1">{{{item1}}}</div>
          <div class="col-sm-4">{{{item2}}}</div>
          <div class="col-sm-4">{{{item3}}}</div>
        </div>

      </td>
    </tr>
    {{/if}}
    {{/each}}
  </table>
</script>
