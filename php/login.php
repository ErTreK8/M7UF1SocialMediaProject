<?php
session_start();

require_once 'conecta_db_persistent.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        try {
            // Verificar usuario
            $query = $db->prepare('SELECT 
                u.*, 
                c.nomCiutat, 
                co.nomComarca, 
                ca.nomComunidad
            FROM Usuario u
            LEFT JOIN Ciutat c ON u.idCiutat = c.idCiutat
            LEFT JOIN Comarca co ON c.idComarca = co.idComarca
            LEFT JOIN ComunidadAutonoma ca ON co.idComunidad = ca.idComunidad
            WHERE (u.nomUsuari = :username OR u.email = :username) 
            AND u.active = 1');
            
            $query->bindParam(':username', $username, PDO::PARAM_STR);
            $query->execute();

            $user = $query->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // Verificar contra
                if (password_verify($password, $user['password'])) { 

                    //ultimo inicio sesion
                    $lastSignIn = date('Y-m-d H:i:s');
                    $updateQuery = $db->prepare('UPDATE Usuario SET lastSignIn = :lastSignIn WHERE IdUsr = :userId');
                    $updateQuery->bindParam(':lastSignIn', $lastSignIn, PDO::PARAM_STR);
                    $updateQuery->bindParam(':userId', $user['IdUsr'], PDO::PARAM_INT);
                    $updateQuery->execute();

                    // guardar cosos en la sesion
                    $_SESSION['user_id'] = $user['IdUsr'];
                    $_SESSION['username'] = $user['nomUsuari'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['image'] = $user['fotoPerfil'];
                    $_SESSION['name'] = $user['nom'];
                    $_SESSION['yearsold'] = $user['edad'];
                    $_SESSION['lastname'] = $user['cognom'];
                    $_SESSION['tlf'] = $user['telefon'];
                    $_SESSION['description'] = $user['descripcio'];
                    $_SESSION['comarca'] = $user['nomComarca'];
                    $_SESSION['ciutat'] = $user['nomCiutat'];
                    $_SESSION['nomComunitat'] = $user['nomComunidad'];
                    header("Location: ../web/home.php");
                    exit;
                } else {
                    $_SESSION['error_message'] = "No se ha podido iniciar sesión, comprueba los datos";
                    header('Location: ./login.php');
                    exit;
                }
            } else {
                $_SESSION['error_message'] = "No se ha podido iniciar sesión, comprueba los datos";
                header('Location: ./login.php');
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = 'Error al verificar usuario: ' . $e->getMessage();
            header('Location: ./login.php');
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Por favor, completa todos los campos.";
        header('Location: ./login.php');
        exit;
    }
} else {
    header("Location: ../index.php");
}
?>