<?php

if (isset($_SESSION['user_id'])) {
    header("Location: ./web/home.php");
    exit;
}
else{
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesion</title>
    <link rel="stylesheet" href="./css/login.css">
</head>
<body>
    <div class="login-container">
        <img src="./img/blanco.png" alt="logo carNation">
        <form action="./php/login.php" method="post">
            <div class="form-group">
                <label for="username">Usuario / Correo</label>
                <input type="text" id="username" name="username" placeholder="Escribe tu nombre o correo" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Escribe tu contraseña" required>
            </div>
            <button type="submit" class="login-btn">Login</button>
        </form>
        <div class="secondary-section">
            No tienes cuenta todavia? <a href="./web/register.php">Registrate</a><br>
            <p><a href="./web/cambiarContrasena.php">¿Has olvidado tu contraseña?</a></p>
            <br>
            <?php
                if (isset($_SESSION['error_message'])) {
                    echo "<h1>" . $_SESSION['error_message'] . "</h1>";
                    unset($_SESSION['error_message']);
                }
                
                if (isset($_SESSION['success_message'])) {
                    echo "<h1>" . $_SESSION['success_message'] . "</h1>";
                    unset($_SESSION['success_message']);
                }
            ?>
        </div>

    </div>
</body>
</html>