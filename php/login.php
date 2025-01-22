<?php
session_start();

require_once 'conecta_db_persistent.php';
// mirar si se ha enviado algo por el post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            // mirar si existe usuario
            $query = $db->prepare('SELECT * FROM Usuario WHERE (nomUsuari = :username OR email = :username)');
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->execute();
            
            $user = $query->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                if ($user['password'] === $password) { 
                    
                    $_SESSION['user_id'] = $user['IdUsr'];
                    $_SESSION['username'] = $user['nomUsuari'];
                    $_SESSION['email'] = $user['email'];
                    
                    header("Location: dashboard.php");
                    exit;
                } else {
                    echo "Contraseña erronea.";
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
}
else
{
    header("Location: ./web/home.php");
}
?>
