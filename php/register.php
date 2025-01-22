<?php
require_once 'conecta_db_persistent.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $verifyPassword = trim($_POST['verify_password']);

    $defaultImagePath = '../img/defaultpfp.webp';
    $profilePhoto = null;

    // mirar si las contraseñas coinciden
    if ($password !== $verifyPassword) {
        echo "Les contrasenyes no coincideixen.";
        exit;
    }

    // imagen a Base64
    if (file_exists($defaultImagePath)) {
        $imageData = file_get_contents($defaultImagePath);
        $profilePhoto = base64_encode($imageData);
    } else {
        echo "Error: No s'ha trobat la imatge predeterminada.";
        exit;
    }

    // mirar si existe etc
    if (!empty($username) && !empty($email) && !empty($password)) {
        try {
            $query = $db->prepare('SELECT COUNT(*) FROM Usuario WHERE nomUsuari = :username OR email = :email');
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->execute();
            $exists = $query->fetchColumn();

            if ($exists > 0) {
                echo "Aquest usuari o email ja existeix.";
                exit;
            }

            $insertQuery = $db->prepare(
                'INSERT INTO Usuario (nomUsuari, password, fotoPerfil, email) 
                 VALUES (:username, :password, :profilePhoto, :email)'
            );

            $insertQuery->bindParam(':username', $username, PDO::PARAM_STR);
            $insertQuery->bindParam(':password', $password, PDO::PARAM_STR); 
            $insertQuery->bindParam(':profilePhoto', $profilePhoto, PDO::PARAM_LOB);
            $insertQuery->bindParam(':email', $email, PDO::PARAM_STR);

            if ($insertQuery->execute()) {
                echo "Usuari creat correctament!";
                header('Location: ../index.html'); 
                exit;
            } else {
                echo "Error en crear l'usuari.";
            }
        } catch (PDOException $e) {
            echo 'Error amb la base de dades: ' . $e->getMessage();
        }
    } else {
        echo "Si us plau, omple tots els camps obligatoris.";
    }
}
?>
