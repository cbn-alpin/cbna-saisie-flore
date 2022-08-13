$(document).ready(function () {

  initAutocomplete();

  $('input[name=champ]').change(function () {
    initAutocomplete();
  });

});

function initAutocomplete() {
  $('#taxons').autocomplete({
    serviceUrl: 'taxon.php',
    dataType: 'json',
    type: 'POST',
    minChars: 1
  });
}
