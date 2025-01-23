<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="../css/register.css">
</head>
<body>
    <div class="signup-container">
        <img src="../img/blanco.png" alt="logo carNation">
        <form action="../php/register.php" method="post">
            <div class="form-group">
                <label for="username">Usuari</label>
                <input type="text" id="username" name="username" placeholder="Choose a username" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>
            </div>
            <div class="form-group">
                <label for="password">Contrasenya</label>
                <input type="password" id="password" name="password" placeholder="Create a password" required>
            </div>
            <div class="form-group">
                <label for="verify_password">Verifica contraseña</label>
                <input type="password" id="verify_password" name="verify_password" placeholder="Verify your password" required>
            </div>
            <button type="submit" class="signup-btn">Sign Up</button>
        </form>
        <div class="secondary-section">
            Ja tens una compte? <a href="../index.php">Login</a>
        </div>
        <div>
            <?php 
                session_start();
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
