<?php
session_start();
require_once 'conecta_db_persistent.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


if (!isset($_GET['code']) || !isset($_GET['mail'])) {
    $_SESSION['error_message'] = "Solicitud inválida.";
    header('Location: ../index.php');
    exit;
}

$code = $_GET['code'];
$email = $_GET['mail'];

$query = $db->prepare("SELECT resetPassExpiry FROM Usuario WHERE email = :email AND resetPassCode = :code");
$query->execute([':email' => $email, ':code' => $code]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error_message'] = "Código inválido.";
    header('Location: ../index.php');
    exit;
}

$expiryTime = strtotime($user['resetPassExpiry']);
$currentTime = time();

if ($expiryTime < $currentTime) {
    $_SESSION['error_message'] = "El código ha expirado.";
    header('Location: ../index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if ($newPassword !== $confirmPassword) {
        $_SESSION['error_message'] = "Las contraseñas no coinciden.";
        header("Location: resetPassword.php?code=$code&mail=$email");
        exit;
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

    $updateQuery = $db->prepare(
        "UPDATE Usuario SET password = :password, resetPassCode = NULL, resetPassExpiry = NULL WHERE email = :email"
    );
    $updateQuery->execute([':password' => $hashedPassword, ':email' => $email]);

    // Enviar email de confirmación
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
    $mail->Subject = 'Cambio de contrasena exitoso';
    $mail->Body = "<h1>Hola,</h1>
                   <p>Tu contraseña ha sido cambiada con éxito. Si no realizaste este cambio, contacta con soporte.</p>";

    $mail->send();

    $_SESSION['success_message'] = "Contraseña actualizada con éxito.";
    header('Location: ../index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Restablecer Contraseña</h2>
        <?php if (isset($_SESSION['error_message'])): ?>
            <p class="error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
        <?php endif; ?>
        <form action="" method="POST">
            <label for="password">Nueva Contraseña:</label>
            <input type="password" name="password" required>
            
            <label for="confirm_password">Confirmar Contraseña:</label>
            <input type="password" name="confirm_password" required>

            <button type="submit">Actualizar Contraseña</button>
        </form>
    </div>
</body>
</html>
