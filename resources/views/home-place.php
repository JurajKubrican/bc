
<script id="places-template"  type="text/x-handlebars-template">
  <table class="table table-striped">
    {{#each places}}
    {{#if symbol}}
    {{else}}
    <tr>
      <td>
        <div class="row" >
          <div class="col-sm-1 collapsed" data-toggle="collapse" data-target="#details-tab-{{id}}" aria-expanded="false" aria-controls="details-tab-{{id}}">
            <span class="hide-if-not-collapsed glyphicon glyphicon-chevron-down"></span>
            <span class="hide-if-collapsed glyphicon glyphicon-chevron-up"></span>
          </div>
          <div class="col-sm-4"><a class="focus-map" data-id="{{id}}" href="#">{{shortName}}</a></div>
          <div class="col-sm-4">{{regionName}}</div>
          <div class="col-sm-1">{{price}}€</div>
        </div>
        <div id="details-tab-{{id}}" class="place-details-tab row collapse">
          <ul class="nav nav-tabs">
            <li class="active"><a data-toggle="tab" href="#details-tab-{{id}}-tab-1">Routes</a></li>
            <li><a data-toggle="tab" href="#details-tab-{{id}}-tab-2">People</a></li>
          </ul>
          <div class="tab-content">
            <div id="details-tab-{{id}}-tab-1" class="tab-pane fade in active">
              <table class="table">
                {{#each routes}}
                <tr><td>{{this.typeName}}</td><td>{{this.priceLow}}€ to {{this.priceHigh}}€</td></tr>
                {{/each}}
              </table>
            </div>
            <div id="details-tab-{{id}}-tab-2" class="tab-pane fade">
              <table class="table">
              {{#each followers}}
                <tr><td><a href="/user/{{this.id}}">{{this.name}}</td></tr>
              {{/each}}
              </table>
            </div>
          </div>
        </div>
      </td>
    </tr>
    {{/if}}
    {{/each}}
  </table>
</script>
