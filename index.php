<?php
if (! file_exists('config.php')) {
    exit('Installation of "Saisie Flore". Please create config.php file from config.sample.php !');
} else {
    include 'config.php';
}
?>

<!DOCTYPE html>
<html lang="fr">
  <head>
    <title>Saisie flore alpine</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  </head>
  <body>
    <div id="content">
      <center><h1>Indiquer la localisation et remplir les champs obligatoires (*)</h1></center>
      <form id="saisi" role="form" method="post" action="send_mail.php" enctype="multipart/form-data" onsubmit="return validerSaisie();">
        <div class="row">
          <div class="form-group col-sm-12">
            <h3>
                <span class="glyphicon glyphicon-map-marker"></span>
                Saisie de la géolocalisation
            </h3>
          </div>

          <div class="form-group col-sm-12 small">
            Pour la saisie des coordonnées GPS (en degrés décimaux), il faut cliquer sur l'outil de recherche (<span class="glyphicon glyphicon-search"></span>),<br />
            puis sur la roue crentée (<span class="glyphicon glyphicon-cog"></span>), et enfin choisir 'Coordonnées GPS' dans la liste déroulante.
          </div>

          <div class="form-group">
            <div id="alert-geometry" class="alert alert-warning alert-dismissible col-sm-12" style="display:none;" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
                Veuillez utiliser la carte pour saisir une géométrie correspondant à votre observation !
            </div>
          </div>

          <div id="ou_carte" class="form-group col-sm-6">
            <div id="map" class="col-sm-6"></div>
          </div>

          <div id="ou_coordonnees" class="form-group">
            <div class="col-sm-6">
              <input type="hidden" id="objet" name="objet" />
              <input type="hidden" id="centroide" name="centroide" />
              <input type="hidden" id="type_objet" name="type_objet" />
            </div>
          </div>

          <div id="qui" class="form-group col-sm-6">
            <div class="col-sm-6">
              <input type="text" id="nom" name="nom" placeholder="Nom *" class="form-control" <?php if(isset($_POST['nom'])) { ?> value="<?php echo $_POST['nom']; ?>"<?php } ?> required />
              <input type="text" id="prenom" name="prenom" placeholder="Pr&eacute;nom *" class="form-control" <?php if(isset($_POST['prenom'])) { ?> value="<?php echo $_POST['prenom']; ?>"<?php } ?> required />
            </div>

            <div class="col-sm-6">
              <input type="text" id="organisme" name="organisme" placeholder="Organisme" class="form-control" <?php if(isset($_POST['organisme'])) { ?> value="<?php echo $_POST['organisme']; ?>"<?php } ?> />
              <input type="email" id="mail" name="mail" placeholder="Adresse mail *" class="form-control" <?php if(isset($_POST['mail'])) { ?> value="<?php echo $_POST['mail']; ?>"<?php } ?> required/>
            </div>
          </div>

          <div id="quand" class="form-group col-sm-6">
            <div class="col-sm-6">
              <input type="text" id="date" name="date" placeholder="Date d'observation *" class="form-control" required />
            </div>
          </div>

          <div id="quoi" class="form-group col-sm-6">
            <div class="input-group">
              <input type="text" id="taxons" name="taxons" placeholder="Taxon" class="form-control" />
              <span class="input-group-btn">
                <button class="btn btn-secondary" type="button" id="ajout-taxon">
                    <span class="glyphicon glyphicon-plus"></span>
                </button>
              </span>
            </div>

            <div id="contenu-taxons"></div>

            <div id="alert-taxons" class="alert alert-warning alert-dismissible col-sm-12" style="display:none;" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                Veuillez saisir un ou plusieurs taxons !
            </div>

            <input type="hidden" name="liste-taxons" id="liste-taxons" required/>
          </div>

          <div id="confiance" class="radio col-sm-6">
            <b>&Ecirc;tes-vous certain de votre d&eacute;termination ?</b>
            <br />
            <label class="radio-inline"><input type="radio" name="determination" id="determination" value="oui" />Oui</label>
            <label class="radio-inline"><input type="radio" name="determination" id="determination" value="non" />Non</label>
          </div>
        </div>

        <div class="row">
          <div id="ecologie" class="form-group col-sm-6">
            <div class="radio col-sm-12">
              <select class="form-control" id="caracteristique" name="caracteristique">
                <option value="0">- Caract&eacute;ristique physionomique sommaire du milieu -</option>
                <option>Zones urbaines</option>
                <option>Routes, chemins, voies ferr&eacute;es, carri&egrave;res</option>
                <option>Cultures, jach&egrave;res, friches</option>
                <option>Eaux et bordures aquatiques</option>
                <option>Tourbi&egrave;res, prairies humides et m&eacute;gaphorbiaies</option>
                <option>Pelouses et prairies s&eacute;ches &agrave; m&eacute;sophiles</option>
                <option>Landes, fourr&eacute;s et haies</option>
                <option>Lisi&egrave;res et for&ecirc;ts</option>
                <option>Rochers, &eacute;boulis et sables</option>
              </select>
              <br />

              <select class="form-control" id="effectif" name="effectif">
                <option value="0">- Effectif -</option>
                <option value="0-10">1 - 9</option>
                <option value="10-100">10 - 99</option>
                <option value="+100">+ 100</option>
              </select>
              <br />

              <textarea class="form-control" id="remarque" name="remarque" placeholder="Remarque"></textarea>
            </div>
          </div>

          <div id="photos" class="form-group col-sm-6">
            <div id="pieces-jointes" class="radio col-sm-12">
              <b>Joindre un fichier </b><span class="glyphicon glyphicon-file"></span><b>
                ou une photo </b><span class="glyphicon glyphicon-camera"></span>
              <input class="form-control" type="file" name="photo[]" id="photo" multiple />
            </div>
          </div>
        </div>

        <div class="row">
          <div id="valid" class="form-group">
              <div class="col-sm-9">
                <cap-widget
                  id="cap"
                  data-cap-api-endpoint="https://captcha.cbn-alpin.fr/<?php echo $saisie_flore_config['captcha-site-key']; ?>/"
                  data-cap-i18n-verifying-label="En cours de vérification..."
                  data-cap-i18n-initial-state="Je suis un humain"
                  data-cap-i18n-solved-label="Je suis un humain"
                  data-cap-i18n-error-label="Erreur"
                  onsolve=""
                ></cap-widget>
              </div>
              <div class="col-sm-3">
                <button type="submit" class="btn btn-primary">
                    Valider
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </div>

    <!-- JQuery -->
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

    <!-- JQuery UI -->
    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/themes/smoothness/jquery-ui.css" />
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.13.2/jquery-ui.min.js"></script>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" />
    <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <!-- Autocomplete -->
    <script type="text/javascript" src="plugins/autocomplete/jquery.autocomplete.js"></script>

    <!-- File Input -->
    <link href="plugins/file-input/fileinput.min.css" media="all" rel="stylesheet" type="text/css" />
    <script type="text/javascript" src="plugins/file-input/fileinput.min.js"></script>
    <script type="text/javascript" src="plugins/file-input/fileinput_locale_fr.js"></script>

    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script type="text/javascript" src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <!-- Leaflet Geometry Management -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.pm@0.12.2/dist/leaflet.pm.css" />
    <script type="text/javascript" src="https://unpkg.com/leaflet.pm@0.12.2/dist/leaflet.pm.min.js"></script>

    <!-- Leaflet Géoportail -->
    <link rel="stylesheet" href="plugins/geoportail/GpPluginLeaflet.css" />
    <script type="text/javascript" src="plugins/geoportail/GpPluginLeaflet.js"></script>

    <!-- Leaflet Coordinates -->
    <link rel="stylesheet" href="plugins/control-coordinates/Control.Coordinates.css" />
    <script type="text/javascript" src="plugins/control-coordinates/Control.Coordinates.js"></script>

    <!-- Leaflet Fullscreen -->
    <link rel="stylesheet" href="plugins/control-fullscreen/Control.FullScreen.css" />
    <script type="text/javascript" src="plugins/control-fullscreen/Control.FullScreen.js"></script>

    <!-- Captcha - Cap -->
    <script src="https://cdn.jsdelivr.net/npm/@cap.js/widget"></script>

    <!-- Global CSS -->
    <link rel="stylesheet" href="css/style.css" />

    <!-- Initialize JS -->
    <script type="text/javascript" src="js/init_map.js"></script>
    <script type="text/javascript" src="js/init_autocomplete.js"></script>
    <script type="text/javascript" src="js/init_fileinput.js"></script>
    <script type="text/javascript" src="js/init_fonction.js"></script>
  </body>
</html>
