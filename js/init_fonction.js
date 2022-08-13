$(document).ready(function () {

  $('[data-toggle="tooltip"]').tooltip();

  var i = 0;
  $(function () {
    $("#date").datepicker({ dateFormat: 'dd/mm/yy' });
  });

  $("#ajout-taxon").click(function () {
    i++;
    var taxon = $("#taxons").val();

    if (taxon == "") {
      $("#taxons").addClass('invalid');
    } else {
      $.ajax({
        url: "taxon_code.php",
        method: "POST",
        data: { nomTaxon: taxon }
      })
        .always(function (taxonCode) {
          var code = JSON.parse(taxonCode);
          if (code.chloris) {
            if (code.taxref == 0) {
              $("#contenu-taxons").append(
                "<p id='" + i + "'>" +
                $("#taxons").val() +
                "<a class='lien-taxon' href='http://www.cbnmc.fr/cartoweb3/Chloris/atlas_mc/fiche_des_mc.php?code_taxon=" +
                code.chloris +
                "' target='_blank' title='Consulter la monographie sur Chloris'>Chloris</a>" +
                "<span class='lien-taxon glyphicon glyphicon-remove' title='Supprimer de la liste' " +
                "onclick='supprimeLigneTaxon(" + i + ")'></span></p>"
              );
            }
            else {
              $("#contenu-taxons").append(
                "<p id='" + i + "'>" +
                $("#taxons").val() +
                "<a class='lien-taxon' href='http://www.cbnmc.fr/cartoweb3/Chloris/atlas_mc/fiche_des_mc.php?code_taxon=" +
                code.chloris +
                "' target='_blank' title='Consulter la monographie sur Chloris'>Chloris</a>" +
                "<a class='lien-taxon' href='http://www.pifh.fr/pifh/pifh/index.php/fiche_descriptive/OuvrirFicheDescriptive/" +
                code.taxref +
                "' target='_blank' title='Consulter la monographie sur le PIFH'>PIFH</a>" +
                "<span class='lien-taxon glyphicon glyphicon-remove' title='Supprimer de la liste' " +
                "onclick='supprimeLigneTaxon(" + i + ")'></span></p>"
              );
            }
          }
          $("#taxons").val('');
          majContenuTaxons();
        });
    }
  });

  $("#taxons").keypress(function () {
    $("#taxons").removeClass('invalid');
  });

  $("*").css("box-sizing", "border-box");

  $(".lb-bt").click(function () {
    $("#ou_carte").css("display", "none");
    $("#ou_coordonnees").css("display", "block");
  });
  $("#bt-saisie-carte").click(function () {
    $("#ou_coordonnees").css("display", "none");
    $("#ou_carte").css("display", "block");
  });
});

function validerSaisie() {
  $('#alert-taxons, #alert-geometry').each(function () {
    $(this).hide();
  });

  let valid = true;
  if ($('#liste-taxons').val() == '') {
    $('#alert-taxons').show();
    valid = false;
  }
  if ($('#objet').val() == '') {
    $('#alert-geometry').show();
    valid = false;
  }
  return valid;
}

function supprimeLigneTaxon(nb) {
  $("p#" + nb).remove();
  majContenuTaxons();
}

function majContenuTaxons() {
  var listeT = "";
  var i = 0;
  $("#contenu-taxons p").each(function () {
    if (i == 0) {
      listeT += ($(this).text()).trim();
    } else {
      listeT += " ; " + ($(this).text()).trim();
    }
    i++;
  });

  var i = $("#contenu-taxons").find("p").length;
  if (i > 1) {
    $("#effectif option[value='0']").prop('selected', true);
    $("#effectif").prop('disabled', true);
    $("input[name='determination']").prop('checked', false);
    $("input[name='determination']").prop('disabled', true);
  }
  else {
    $("#effectif").prop('disabled', false);
    $("input[name='determination']").prop('disabled', false);
  }

  $("#liste-taxons").val(listeT);
}
