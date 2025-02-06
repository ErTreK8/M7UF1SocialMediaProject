<?php
session_start();
require_once 'conecta_db_persistent.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $verifyPassword = trim($_POST['verify_password']);

    $defaultImagePath = '../img/defaultpfp.webp';
    $profilePhoto = null;

    // Verificar contra
    if ($password !== $verifyPassword) {
        $_SESSION['error_message'] = "Las contraseñas no coinciden.";
        header('Location: ../web/register.php');
        exit;
    }

    // Imagen a Base64
    if (file_exists($defaultImagePath)) {
        $imageData = file_get_contents($defaultImagePath);
        $profilePhoto = base64_encode($imageData);
    } else {
        $_SESSION['error_message'] = "Error: No se ha encontrado la imagen predeterminada.";
        header('Location: ../web/register.php');
        exit;
    }

    // Verificar campos
    if (!empty($username) && !empty($email) && !empty($password)) {
        try {
            $query = $db->prepare('SELECT COUNT(*) FROM Usuario WHERE nomUsari = :username OR email = :email');
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->bindParam(':email', $email, PDO::PARAM_STR);
            $query->execute();
            $exists = $query->fetchColumn();

            if ($exists > 0) {
                $_SESSION['error_message'] = "Este Usuario o email ya existe.";
                header('Location: ../web/register.php');
                exit;
            }

            // Encriptar coso
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $creationDate = date('Y-m-d H:i:s');

            $insertQuery = $db->prepare(
                'INSERT INTO Usuario (nomUsari, password, fotoPerfil, email, creationDate, active) 
                 VALUES (:username, :password, :profilePhoto, :email, :creationDate, :active)'
            );

            $active = 1; 

            $insertQuery->bindParam(':username', $username, PDO::PARAM_STR);
            $insertQuery->bindParam(':password', $hashedPassword, PDO::PARAM_STR); 
            $insertQuery->bindParam(':profilePhoto', $profilePhoto, PDO::PARAM_LOB);
            $insertQuery->bindParam(':email', $email, PDO::PARAM_STR);
            $insertQuery->bindParam(':creationDate', $creationDate, PDO::PARAM_STR);
            $insertQuery->bindParam(':active', $active, PDO::PARAM_INT);

            if ($insertQuery->execute()) {
                $_SESSION['success_message'] = "Registro Correcto";
                header('Location: ../index.php'); 
                exit;
            } else {
                $_SESSION['error_message'] = "Error al crear el usuario.";
                header('Location: ../web/register.php');
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error con la base de datos: ' . $e->getMessage();
            header('Location: ../web/register.php');
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Por favor, completa todos los campos obligatorios.";
        header('Location: ../web/register.php');
        exit;
    }
}
?>