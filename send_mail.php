
<?php
include 'config.php';


$temp = '';

//===== Vérification du token reCAPTCHA =====
$cap_token = $_POST['cap-token'] ?? '';
$cap_body = json_encode([
    'secret' => $saisie_flore_config['captcha-secret-key'],
    'response' => $cap_token,
]);
$cap_opts = ['http' =>
    [
        'method'  => 'POST',
        'header'  => 'Content-Type: application/json'."\r\n",
        'content' => $cap_body,
        'timeout' => 10,
    ]
];
//echo '<pre>'.print_r($_POST, true)."\n".print_r($cap_opts, true).'</pre>';
$cap_context  = stream_context_create($cap_opts);
$cap_url = 'https://captcha.cbn-alpin.fr/'.$saisie_flore_config['captcha-site-key'].'/siteverify';
$cap_verify = file_get_contents($cap_url, false, $cap_context);

if ($cap_verify === false) {
    exit('Erreur de connexion au service reCAPTCHA');
} else {
    $cap_response = json_decode($cap_verify, true);
    if (!$cap_response['success']) {
        exit('reCAPTCHA invalide');
    }
}

//===== Infos où =====
$objet = $_POST['objet'];
$centroide = $_POST['centroide'];
$type_objet = $_POST['type_objet'];

//===== Infos qui =====
$nom = $_POST['nom'];
$prenom = $_POST['prenom'];
$expediteur = "$prenom $nom";
$mail = trim($_POST['mail']);
$organisme = '';
if (isset($_POST['organisme'])) {
    $organisme = $_POST['organisme'];
}

//===== Infos quand =====
$date = $_POST['date'];

//===== Infos quoi =====
$listeTaxons = '';
if (!empty($_POST['liste-taxons'])) {
    $listeTaxons .= trim($_POST['liste-taxons']);

    if (!empty($_POST['taxons'])) {
        $listeTaxons .= ' ; '.trim($_POST['taxons']);
    }
} else {
    if (!empty($_POST['taxons'])) {
        $listeTaxons .= trim($_POST['taxons']);
    }
}
$listeTaxons = str_replace(['Chloris', 'PIFH'], '', $listeTaxons);
$listeTaxons = str_replace('      ', '',  $listeTaxons);
$taxons = explode(' ; ', $listeTaxons);

//===== Infos optionnelles =====
$determination = '-';
$caracteristique = '-';
$effectif = '-';
$remarque = '-';
if (isset($_POST['determination'])) {
    $determination = $_POST['determination'];
}
if ($_POST['caracteristique'] != '0') {
    $caracteristique = $_POST['caracteristique'];
}
if ($_POST['effectif'] != '0') {
    $effectif = $_POST['effectif'];
}
if (isset($_POST['remarque'])) {
    $remarque = $_POST['remarque'];
}

//===== Vérification et préparation des pièces jointes =====
$lesFichiers = [];
if (isset($_FILES['photo']['name']) && array_sum($_FILES['photo']['name']) > 0) {
    $fichiersAJoindre = [];
    foreach ($_FILES['photo'] as $key => $value) {
        foreach ($value as $k => $v) {
            $fichiersAJoindre[$k][$key] = $v;
        }
    }
    $arrayFiles = [];
    foreach ($fichiersAJoindre as $arrayFiles) {
        $aboutFile = pathinfo($arrayFiles['name']);
        $lesFichiers[] = [
            'chemin' => getenv('TMP').'/'.$arrayFiles['tmp_name'],
            'nom' => $aboutFile['filename'],
            'extension' => $aboutFile['extension'],
            'mimeType' => $arrayFiles['type'],
            'contenu' => chunk_split(base64_encode(file_get_contents($arrayFiles['tmp_name'])))
        ];
    }
}

$to = $saisie_flore_config['mail-to'];

if (preg_match('#@(hotmail|live|msn).[a-z]{2,4}$#', $to)) {
    $passage_ligne = "\n";
} else {
    $passage_ligne = "\r\n";
}

$boundary = '-----='.md5(rand());
$boundary_alt = '-----='.md5(rand());

$sujet = 'Saisie en ligne';
$message_html = "<b>Observateur :</b><br /><span id='expediteur'>$expediteur</span><br /><u>Organisme</u> : <span id='organisme'>$organisme</span><br /><span id='mail'>$mail</span>
    <br /><br />
    <b>Observation :</b><br />
        <u>Localisation</u> : <ul style='list-style-type: none;'>
            <li>Objet : <span id='objet'>$objet</span></li>
            <li>Centroide : $centroide</li>
            <li>Type objet : <span id='type'>$type_objet</span> (1: Polygone, 2: Polyline, 3: Point)</li>
        </ul>
        <u>Date</u> : <span id='date'>$date</span><br />
        <u>Taxons</u> : <ul style='list-style-type: none;'>";
foreach ($taxons as $k => $v) {
    $message_html .= "<li>".str_replace('&nbsp;', '', $v)."</li>";
}
$message_html .= "</ul><br />
    <b>Informations compl&eacute;mentaires : </b><br />
        <u>D&eacute;termination</u> : <span id='determination'>$determination</span><br />
        <u>Caract&eacute;ristique</u> : <span id='caracteristique'>$caracteristique</span><br />
        <u>Effectif</u> : <span id='effectif'>$effectif</span><br />
        <u>Remarque</u> : <span id='remarque'>$remarque</span><br /><br />";
$message_txt = strip_tags($message_html);

$headers = "Reply-To: $expediteur <$mail>".$passage_ligne;

$headers .= "MIME-Version: 1.0".$passage_ligne;
$headers .= "Content-Type: multipart/mixed;".$passage_ligne." boundary=\"".$boundary."\"".$passage_ligne;

//===== Création du message =====
$message = $passage_ligne."--".$boundary.$passage_ligne;
$message .= "Content-Type: multipart/alternative;".$passage_ligne." boundary=\"".$boundary_alt."\"".$passage_ligne;

$message .= $passage_ligne."--".$boundary_alt.$passage_ligne;

$message .= "Content-Type: text/plain; charset=\"ISO-8859-1\"".$passage_ligne;
$message .= "Content-Transfer-Encoding: 8bit".$passage_ligne;
$message .= $passage_ligne.$message_txt.$passage_ligne;

$message .= $passage_ligne."--".$boundary_alt.$passage_ligne;

$message .= "Content-Type: text/html; charset=\"ISO-8859-1\"".$passage_ligne;
$message .= "Content-Transfer-Encoding: 8bit".$passage_ligne;
$message .= $passage_ligne.$message_html.$passage_ligne;

$message .= $passage_ligne."--".$boundary_alt."--".$passage_ligne;

//===== Pièce(s) jointe(s) =====
foreach ($lesFichiers as $fileArray) {
    if (!empty($fileArray['nom'])) {
        $message.= $passage_ligne."--".$boundary.$passage_ligne;

        $message.="Content-Type: ".$fileArray['mimeType']."; name=\"".$fileArray['nom'].".".$fileArray['extension']."\"".$passage_ligne;
        $message.="Content-Transfer-Encoding: base64".$passage_ligne;
        $message.="Content-Disposition: attachment; filename=\"".$fileArray['nom'].".".$fileArray['extension']."\"".$passage_ligne;
        $message.= $passage_ligne.$fileArray['contenu'].$passage_ligne.$passage_ligne;
    }
}

//===== Fermeture du message =====
$message .= $passage_ligne."--".$boundary."--".$passage_ligne;

$mail_success = false;
if (mail($to, $sujet, $message, $headers)) {
    $mail_success = true;
}
?>
<html>
    <head>
        <title>Saisie flore alpine | CBNA</title>
    </head>
    <body>
        <?php if ($mail_success) : ?>
            <div class="alert alert-success">
                <strong>Votre message &agrave; bien &eacute;t&eacute; envoy&eacute;</strong>
            </div>

            <form role="form" action="index.php" method="post">
                <input type="hidden" id="nom" name="nom" value="<?php echo $nom; ?>" />
                <input type="hidden" id="prenom" name="prenom" value="<?php echo $prenom; ?>" />
                <input type="hidden" id="organisme" name="organisme" value="<?php echo $organisme; ?>" />
                <input type="hidden" id="mail" name="mail" value="<?php echo $mail; ?>" />
                <button type="submit" class="btn btn-default">Nouvelle saisie</button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger">
                <strong>Votre message n'&agrave; pas pu &ecirc;tre envoy&eacute;s !</strong>
            </div>

            <form>
                <input type="button" class="btn btn-default" value="Retour" onclick="history.go(-1)">
            </form>
        <?php endif; ?>

        <!-- JQuery -->
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

        <!-- Bootstrap -->
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    </body>
</html>
