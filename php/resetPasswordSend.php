<?php
session_start();
require_once 'conecta_db_persistent.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $resetCode = hash('sha256', random_bytes(64));
    $expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));

    $query = $db->prepare("UPDATE Usuario SET resetPassCode = :code, resetPassExpiry = :expiry WHERE email = :email");
    $query->execute([':code' => $resetCode, ':expiry' => $expiry, ':email' => $email]);

    $resetLink = "http://localhost/Proyecto/M7UF1SocialMediaProject/php/resetPassword.php?code=$resetCode&mail=$email";

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
    $mail->isHTML(true);
    $mail->Subject = 'Restablecimiento de contrasena';
    $mail->Body = "<h1>Recuperación de contraseña</h1>
                   <p>Para restablecer tu contraseña, haz clic en el siguiente enlace antes de que expire:</p>
                   <a href='$resetLink'>Restablecer contraseña</a>";

    $mail->send();
    $_SESSION['success_message'] = "Correo de recuperación enviado.";
    header('Location: ../index.php');
}
?>
