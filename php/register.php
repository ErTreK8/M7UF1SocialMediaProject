<?php
session_start();
require_once 'conecta_db_persistent.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $verifyPassword = trim($_POST['verify_password']);

    if ($password !== $verifyPassword) {
        $_SESSION['error_message'] = "Las contraseñas no coinciden.";
        header('Location: ../web/register.php');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
    $activationCode = hash('sha256', random_bytes(64));
    $creationDate = date('Y-m-d H:i:s');
    $active = 0;

    try {
        $query = $db->prepare('SELECT COUNT(*) FROM Usuario WHERE nomUsuari = :username OR email = :email');
        $query->execute([':username' => $username, ':email' => $email]);
        if ($query->fetchColumn() > 0) {
            $_SESSION['error_message'] = "Este Usuario o email ya existe.";
            header('Location: ../web/register.php');
            exit;
        }

        $insertQuery = $db->prepare(
            'INSERT INTO Usuario (nomUsuari, password, email, creationDate, active, activationCode) 
             VALUES (:username, :password, :email, :creationDate, :active, :activationCode)'
        );

        $insertQuery->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
            ':email' => $email,
            ':creationDate' => $creationDate,
            ':active' => $active,
            ':activationCode' => $activationCode
        ]);

        // enlace de activación
        $activationLink = "http://localhost/Proyecto/M7UF1SocialMediaProject/php/mailCheckAccount.php?code=$activationCode&mail=$email";
        
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'eric.garciag@educem.net';
        $mail->Password = 'hnbj woau zegg biyg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('eric.garciag@educem.net', 'Car Nation');
        $mail->addAddress($email);
        $mail->AddEmbeddedImage('../img/negro.png', 'logo' , 'negro.png');
        $mail->isHTML(true);
        $mail->Subject = 'Activa tu cuenta';
        $mail->Body = "<h1>Bienvenido, $username</h1>
                       <p>Por favor, activa tu cuenta haciendo clic en el siguiente enlace:</p>
                       <a href='$activationLink'>Activar cuenta</a>
                       <img alt='PHPMailer' src='cid:logo'>";


        $mail->send();
        $_SESSION['success_message'] = "Registro correcto. Revisa tu correo electrónico para activarlo.";
        header('Location: ../index.php');
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error al enviar el correo: ' . $mail->ErrorInfo;
        header('Location: ../web/register.php');
    }
}
?>
