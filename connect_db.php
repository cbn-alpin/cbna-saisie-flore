<?php

    include 'config.php';

    $db_host = $saisie_flore_config['db-host'];
    $db_port = $saisie_flore_config['db-port'];
    $db_name = $saisie_flore_config['db-name'];
    $db_user = $saisie_flore_config['db-user'];
    $db_password = $saisie_flore_config['db-password'];

    $dbconn = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_password");
?>
