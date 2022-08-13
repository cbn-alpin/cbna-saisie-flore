<?php
    if (isset($_POST['query'])) {
        $q = $_POST['query'];

        $nb_mots = explode(' ', $q);
        $suggestions['suggestions'] = array();

        // Connexion à la base de données
        include 'connect_db.php';

        if (count($nb_mots) > 1) {
            $q = str_replace(' ', '% ', $q);
            // Requête SQL
        }

        $q .= '%';

        // Prépare une requête pour l'exécution
        $result = pg_prepare($dbconn, 'my_query', 'SELECT nom_complet FROM taxon WHERE nom_complet ILIKE $1 LIMIT 40;');

        // Exécute la requête préparée. Notez qu'il n'est pas nécessaire d'échapper
        $result = pg_execute($dbconn, 'my_query', [$q]);

        if (!$result) {
            echo "Une erreur s'est produite.\n";
            exit();
        }

        while ($row = pg_fetch_row($result)) {
            $suggestions['suggestions'][] = mb_convert_encoding($row[0], 'UTF-8');
        }

        // Retour des données au format JSON pour le plugin
        echo json_encode($suggestions);
    }
?>
