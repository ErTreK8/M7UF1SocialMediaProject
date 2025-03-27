<?php
$cadena_connexio = 'mysql:dbname=m7redsocial2;host=localhost:3335';
$usuari = 'root';
$passwd = '';
try {
    //Creem una connexió persistent a BDs
    $db = new PDO($cadena_connexio, $usuari, $passwd,
        array(PDO::ATTR_PERSISTENT => true));

    if ($db != null) {
        // echo '<pre>';.
        // echo "Connexió establerta! \n ";
        // echo '</pre>';
    }

} catch (PDOException $e) {
    echo 'Error amb la BDs: ' . $e->getMessage() . '<br>';
}
