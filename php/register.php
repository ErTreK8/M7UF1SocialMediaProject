<?php
require_once 'conecta_db_persistent.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $verifyPassword = trim($_POST['verify_password']);

    $defaultImagePath = '../img/defaultpfp.webp';
    $profilePhoto = null;

    if ($password !== $verifyPassword) {
        echo "Las contraseñas no coinciden.";
        exit;
    }

    // Imagen a Base64
    if (file_exists($defaultImagePath)) {
        $imageData = file_get_contents($defaultImagePath);
        $profilePhoto = base64_encode($imageData);
    } else {
        echo "Error: No se ha encontrado la imagen predeterminada.";
        exit;
    }

    // Verificaciones
    if (!empty($username) && !empty($email) && !empty($password)) {
        try {
            $query = $db->prepare('SELECT COUNT(*) FROM Usuario WHERE nomUsuari = :username OR email = :email');
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->execute();
            $exists = $query->fetchColumn();

            if ($exists > 0) {
                echo "Este usuario o email ya existe.";
                exit;
            }

            // Encriptar la contra
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $insertQuery = $db->prepare(
                'INSERT INTO Usuario (nomUsuari, password, fotoPerfil, email) 
                 VALUES (:username, :password, :profilePhoto, :email)'
            );

            $insertQuery->bindParam(':username', $username, PDO::PARAM_STR);
            $insertQuery->bindParam(':password', $hashedPassword, PDO::PARAM_STR); 
            $insertQuery->bindParam(':profilePhoto', $profilePhoto, PDO::PARAM_LOB);
            $insertQuery->bindParam(':email', $email, PDO::PARAM_STR);
            //todo bien
            if ($insertQuery->execute()) {
                echo "Usuario creado correctamente!";
                header('Location: ../index.html'); 
                exit;
            } else {
                echo "Error al crear el usuario.";
            }
        } catch (PDOException $e) {
            echo 'Error con la base de datos: ' . $e->getMessage();
        }
    } else {
        echo "Por favor, completa todos los campos obligatorios.";
    }
}
?>