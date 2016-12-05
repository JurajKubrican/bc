(function ($,Bloodhound) {
  ('use strict');

  /*
  *
  */
  $(document).ready(function() {
    var r2rAutocomplete = new Bloodhound({
      datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
      queryTokenizer: Bloodhound.tokenizers.whitespace,
      remote: {
        url: 'https://www.rome2rio.com/api/1.2/json/autocomplete?query=%QUERY&resultType=r2r',
        wildcard: '%QUERY',
        filter: function(response) {
          return response.places;
        }
      }
    });
    $('#home-place').typeahead({ hint: true,
      highlight: true,
      minLength: 1
    }, {
      name: 'best-pictures',
      displayKey: function(countries) {
        return countries.longName;
      },
      source: r2rAutocomplete
    }).on('typeahead:selected', function(event, data){
      $('#home-data').val(JSON.stringify(data));
    });
  });

}($, Bloodhound ));
