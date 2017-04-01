
<script id="places-template"  type="text/x-handlebars-template">
  <table class="table table-striped">
    {{#each places}}
      {{#if symbol}}
      {{else}}
      <tr>
        <td>
          <div class="row" data-toggle="collapse" data-target="#details-tab-{{id}}" aria-expanded="false" aria-controls="details-tab-{{id}}">
            <div class="col-sm-5"><a href="#">{{shortName}}</a></div>
            <div class="col-sm-5">{{regionName}}</div>
            <div class="col-sm-1">{{price}}€</div>
            <div class="col-sm-1"><a class="delete glyphicon glyphicon-remove" data-id="{{id}}" href="#"></a></div>
          </div>
          <div id="details-tab-{{id}}" class="place-details-tab row collapse">
            <ul class="nav nav-tabs">
              <li class="active"><a data-toggle="tab" href="#details-tab-{{id}}-tab-1">Prices</a></li>
              <li><a data-toggle="tab" href="#details-tab-{{id}}-tab-2">People</a></li>
            </ul>
            <div class="tab-content">
              <div id="details-tab-{{id}}-tab-1" class="tab-pane fade in active">
                <div id="details-tab-plot-{{id}}" class="details-tab-plot" data-history="{{history}}"></div>
              </div>
              <div id="details-tab-{{id}}-tab-2" class="tab-pane fade">
                {{#each followers}}
                  {{this}}<br>
                {{/each}}
              </div>
            </div>
          </div>
        </td>
      </tr>
      {{/if}}
    {{/each}}
  </table>
</script>
