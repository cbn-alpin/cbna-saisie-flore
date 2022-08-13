<?php
    if (isset($_POST['nomTaxon']) && !empty($_POST['nomTaxon'])) {
        $nom_taxon = $_POST['nomTaxon'];
        $code = [];
        $code['taxref'] = 0;

        include 'connect_db.php';

        // Prépare une requête pour l'exécution
        $result = pg_prepare($dbconn, 'requete', 'SELECT code_taxon FROM taxon WHERE nom_complet = $1;');

        // Exécute la requête préparée. Notez qu'il n'est pas nécessaire d'échapper
        $result = pg_execute($dbconn, 'requete', array($nom_taxon));

        while ($row = pg_fetch_row($result)) {
            $code['chloris'] = $row[0];
        }

        if ($file = fopen('chloris_taxref.csv', 'r')) {
            while ($ligne = fgetcsv($file, 0, ';')) {
                if ($ligne[0] == $code['chloris']) {
                    $code['taxref'] = $ligne[1];
                    break;
                }
            }
        }
        echo json_encode($code);
    }
?>
