<?php
if (isset($_SESSION['user_id'])) {
    header("Location: ./home.php");
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
    <title>Registro</title>
    <link rel="stylesheet" href="../css/register.css">
</head>
<body>
    <div class="signup-container">
        <img src="../img/blanco.png" alt="logo carNation">
        <form action="../php/register.php" method="post">
            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" placeholder="Escribe tu nombre de usuario" required>
            </div>
            <div class="form-group">
                <label for="email">Correo</label>
                <input type="email" id="email" name="email" placeholder="Escribe tu correo" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Escribe tu contraseña" required>
            </div>
            <div class="form-group">
                <label for="verify_password">Verifica contraseña</label>
                <input type="password" id="verify_password" name="verify_password" placeholder="Vuelve a escribir tu contraseña" required>
            </div>
            <button type="submit" class="signup-btn">Registrate</button>
        </form>
        <div class="secondary-section">
            Ya tienes cuenta? <a href="../index.php">Inicia sesion</a>
        </div>
        <div>
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
