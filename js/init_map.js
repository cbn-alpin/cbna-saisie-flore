var map;

function initMap() {
  // Création de la carte
  map = L.map('map', {
    fullscreenControl: true,
    fullScreenControlOptions: {
      position: 'topleft'
    }
  }).setView([46.5, 3], 6);

  // Création des couche
  var lyrOrtho = L.geoportalLayer.WMTS({
    layer: 'ORTHOIMAGERY.ORTHOPHOTOS',
  });
  var lyrMaps = L.geoportalLayer.WMTS({
    layer: 'GEOGRAPHICALGRIDSYSTEMS.PLANIGNV2',
  });
  var lyrCad = L.geoportalLayer.WMS(
    {
      layer: 'CADASTRALPARCELS.PARCELS',
    },
    {
      transparent : true,
      format: 'image/png',
      styles: 'bdparcellaire_o',
    }
  );
  map.addLayer(lyrOrtho);
  map.addLayer(lyrMaps);
  map.addLayer(lyrCad);

  // Création du controle des couches
  var layerSwitcher = L.geoportalControl.LayerSwitcher({
    layers: [
      {
        layer: lyrCad,
        config: {
          title: 'Parcelles cadastrales',
          visibility: false,
        }
      },
      {
        layer: lyrOrtho,
        config: {
          title: 'Photographies aériennes',
          visibility: false,
        }
      },
      {
        layer: lyrMaps,
        config: {
          title: 'Fond de cartes IGN',
        }
      }
    ]
  });
  map.addControl(layerSwitcher);

  // Affichage de l'échelle
  L.control.scale({
    metric: true,
    imperial: false
  }).addTo(map);

  // Affichage des coordonnées GPS
  L.control.coordinates({
    position: "bottomleft",
    decimals: 2,
    decimalSeperator: ",",
    labelTemplateLat: "Latitude: {y}",
    labelTemplateLng: "Longitude: {x}"
  }).addTo(map);

  // Création barre de recherche adresse
  var searchCtrl = L.geoportalControl.SearchEngine({});
  map.addControl(searchCtrl);

  // Ajout de la recherche de coordonnées GPS
  var code;
  $('.GPadvancedSearchCode').change(function () {
    code = $(this).attr('id');
    var codeSelect = code.split('-');
    code = codeSelect[1];

    if ($(this).val() == 'PositionGPS') {
      $("#GPadvancedSearchForm-" + code).attr("onsubmit", "setCoordonneesGPS(" + code + ");");
      var contenuForm = '<div id="PositionGPS-' + code + '">';
      contenuForm += '<div class="GPflexInput">';
      contenuForm += '<label class="GPadvancedSearchFilterLabel" for="latitude" title="Latitude">Latitude</label>';
      contenuForm += '<input id="latitude" class="GPadvancedSearchFilterInput" tyep="text" name="latitude" />';
      contenuForm += '</div>';
      contenuForm += '<div class="GPflexInput">';
      contenuForm += '<label class="GPadvancedSearchFilterLabel" for="longitude" title="Longitude">Longitude</label>';
      contenuForm += '<input id="longitude" class="GPadvancedSearchFilterInput" tyep="text" name="longitude" />';
      contenuForm += '</div>';
      contenuForm += '</div>';
      $('#GPadvancedSearchFilters-' + code).append(contenuForm);

      var btSubmit = '<input type="submit" id="GPadvancedSearchSubmit-GPS" class="GPinputSubmit" value="Positionner" />';
      $("#GPadvancedSearchForm-" + code).append(btSubmit);
      $('#GPadvancedSearchSubmit-' + code).addClass('hide');
    } else {
      $("#GPadvancedSearchSubmit-GPS").remove();
      $('#GPadvancedSearchSubmit-' + code).removeClass('hide');

      $("#GPadvancedSearchForm-" + code).removeAttr("onsubmit");
    }
  });
  var option = document.createElement('option');
  option.value = 'PositionGPS';
  option.text = 'Coordonnées GPS';
  $('select.GPadvancedSearchCode').append(option);

  // Création de la toolbar de dessins
    var options = {
    position: 'bottomright',
    drawMarker: true,  // adds button to draw markers
    drawPolygon: true,  // adds button to draw a polygon
    drawPolyline: true,  // adds button to draw a polyline
    editPolygon: true,  // adds button to toggle global edit mode
    deleteLayer: true   // adds a button to delete layers
  };
  map.pm.addControls(options);

  map.on('pm:create', function (e) {
    var layer = e.layer;
    var target = e.target;
    var typeGeo = e.shape;
    switch (typeGeo) {
      case "Poly":
        var tabPoly = layer._latlngs[0];
        var bounds = L.latLngBounds(tabPoly);
        var centroide = bounds.getCenter();
        var objet = "";
        for (var i = 0; i < tabPoly.length; i++) {
          objet += tabPoly[i];
        }
        remplissageInput(objet, centroide, "1");
        layer.on('pm:edit', function (l) { editObjet(l, "1", tabPoly); }).on('remove', function (r) { removeObjet(map); });
        break;
      case "Line":
        var tabLine = layer._latlngs;
        var bounds = L.latLngBounds(tabLine);
        var centroide = bounds.getCenter();
        var objet = "";
        for (var i = 0; i < tabLine.length; i++) {
          objet += tabLine[i];
        }
        remplissageInput(objet, centroide, "2");
        layer.on('pm:edit', function (l) { editObjet(l, "2", tabLine); }).on('remove', function (r) { removeObjet(map); });
        break;
      case "Marker":
        var marker = e.marker;
        remplissageInput(marker._latlng, marker._latlng, "3");
        marker.on('pm:edit', function (m) { editMarker(m); }).on('remove', function (r) { removeObjet(map); });
        break;
      default:
        console.log("oups");
        break;
    }
  });
}

function recupCentroide(centroide_objet) {
  var coord = centroide_objet.replace("LatLng(", "");
  coord = coord.replace(")", "");
  var latlng = coord.split(", ");
  return latlng;
}

function recupCoord(objet) {
  var coord = objet.split(")");
  coord = coord.filter(function (val) {
    if (val == '' || val == NaN || val == undefined || val == null) {
      return false;
    }
    return true;
  });
  for (var i = 0; i < coord.length; i++) {
    coord[i] = coord[i].replace("LatLng(", "");
    coord[i] = coord[i].split(", ");
  }
  return coord;
}

function setCoordonneesGPS(code) {
  var lat = $("#latitude").val().replace(',', '.');
  var lon = $("#longitude").val().replace(',', '.');
  if (lat != "" && lon != "") {
    $("#GPgeocodeResultsList-" + code).hide();

    map.setView([lat, lon], 12);
    var marker = L.marker([lat, lon]).addTo(map);

    remplissageInput(marker._latlng, marker._latlng, "3");
    marker.on('pm:edit', function (m) { editMarker(m); }).on('remove', function (r) { removeObjet(map); });
  }
}

function editMarker(m) {
  var modifMarker = m.target;
  remplissageInput(modifMarker._latlng, modifMarker._latlng, "3");
}

function editObjet(l, type_objet, tab) {
  var modifTarget = l.target;
  if (type_objet == "1") {
    var modifTabPoly = modifTarget._latlngs[0];
  } else {
    var modifTabPoly = modifTarget._latlngs;
  }
  var modifBounds = L.latLngBounds(tab);
  var modifCentroide = modifBounds.getCenter();
  var modifObjet = "";
  for (var i = 0; i < modifTabPoly.length; i++) {
    modifObjet += modifTabPoly[i];
  }
  remplissageInput(modifObjet, modifCentroide, type_objet);
}

function removeObjet(map) {
  remplissageInput();

  map.pm.enableDraw('Marker');
  $('.leaflet-pm-toolbar a:nth-child(1)').removeClass('hide');
  $('.leaflet-pm-toolbar a:nth-child(2)').removeClass('hide');
  $('.leaflet-pm-toolbar a:nth-child(3)').removeClass('hide');
}

function remplissageInput(objet, centroide, type_objet) {
  $("#objet").val(objet);
  $("#centroide").val(centroide);
  $("#type_objet").val(type_objet);

  map.pm.disableDraw('Marker');
  $('.leaflet-pm-toolbar a:nth-child(1)').addClass('hide');
  $('.leaflet-pm-toolbar a:nth-child(2)').addClass('hide');
  $('.leaflet-pm-toolbar a:nth-child(3)').addClass('hide');
}

$(document).ready(() => {
  Gp.Services.getConfig({
    customConfigFile: "/plugins/geoportail/autoconf.custom.json",
    timeOut: 20000,
    onSuccess: initMap,
  });
});

