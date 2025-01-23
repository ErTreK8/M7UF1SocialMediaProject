<?php
session_start();

require_once 'conecta_db_persistent.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            // mirar si existe el usuario
            $query = $db->prepare('SELECT * FROM Usuario WHERE (nomUsuari = :username OR email = :username)');
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->execute();
            
            $user = $query->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // verificar contraseña 
                if (password_verify($password, $user['password'])) { 
                    $_SESSION['user_id'] = $user['IdUsr'];
                    $_SESSION['username'] = $user['nomUsuari'];
                    $_SESSION['email'] = $user['email'];
                    header("Location: ../web/home.php");
                    exit;
                } else {
                    echo "Contraseña incorrecta.";
                }
            } else {
                echo "Usuario no encontrado.";
            }
        } catch (PDOException $e) {
            echo 'Error al verificar usuario: ' . $e->getMessage();
        }
    } else {
        echo "Por favor, completa todos los campos.";
    }
} else {
    header("Location: ../index.html");
}
?>