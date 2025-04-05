<?php
session_start();
require_once 'conecta_db_persistent.php';

if (isset($_SESSION['user_id'])) {
    $username = $_SESSION['username'];
    
    // Consulta para obtener los datos del usuario y su ciudad
    $query = $db->prepare('SELECT 
                u.*, 
                c.nomCiutat
            FROM Usuario u
            LEFT JOIN Ciutat c ON u.idCiutat = c.idCiutat
            WHERE (u.nomUsuari = :username OR u.email = :username) 
            AND u.active = 1');
            
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->execute();

    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Guardar datos del usuario en la sesión
        $_SESSION['user_id'] = $user['IdUsr'];
        $_SESSION['username'] = $user['nomUsuari'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['image'] = $user['fotoPerfil'];
        $_SESSION['name'] = $user['nom'];
        $_SESSION['yearsold'] = $user['edat']; // Usa 'edat' en lugar de 'edad'
        $_SESSION['lastname'] = $user['cognom'];
        $_SESSION['tlf'] = $user['telefon'];
        $_SESSION['description'] = $user['descripcio'];
        $_SESSION['ciutat'] = $user['nomCiutat']; // Asigna directamente la ciudad del usuario
    }
}
?>