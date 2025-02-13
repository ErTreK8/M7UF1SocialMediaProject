<?php
session_start();
require_once 'conecta_db_persistent.php';

if (isset($_GET['code']) && isset($_GET['mail'])) {
    $code = $_GET['code'];
    $email = $_GET['mail'];

    $query = $db->prepare("SELECT * FROM Usuario WHERE email = :email AND activationCode = :code");
    $query->execute([':email' => $email, ':code' => $code]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $update = $db->prepare("UPDATE Usuario SET active = 1, activationCode = NULL, activationDate = NOW() WHERE email = :email");
        $update->execute([':email' => $email]);
        $_SESSION['success_message'] = "Cuenta activada correctamente.";
    } else {
        $_SESSION['error_message'] = "Código de activación inválido.";
    }
}

header('Location: ../index.php');
exit;
?>
