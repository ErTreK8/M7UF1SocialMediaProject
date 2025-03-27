<?php
require_once 'conecta_db_persistent.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // Obtener datos del usuario junto con la ciudad
    $query = $db->prepare("SELECT u.nomUsari, u.nom, u.cognom, u.email, u.telefon, u.descripcio, u.fotoPerfil, 
                                  u.edad, u.Calle, c.nomCiutat 
                           FROM Usuario u
                           LEFT JOIN Ciutat c ON u.idCiutat = c.idCiutat
                           WHERE u.IdUsr = :user_id");
    $query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $query->execute();
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: home.php");
        exit;
    }

    // Verificar si la foto de perfil está en Base64
    if (!empty($user['fotoPerfil']) && str_starts_with($user['fotoPerfil'], 'data:image')) {
        $fotoPerfil = $user['fotoPerfil']; // Ya es Base64, se usa directamente
    } else {
        $fotoPerfil = '../imgPerfil/generic.png'; // Imagen por defecto
    }

} catch (PDOException $e) {
    die("Error al obtener los datos del usuario: " . $e->getMessage());
}
?>
