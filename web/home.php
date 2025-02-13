<?php 
    require_once '../php/comprobar_Login.php';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Home</title>
        <link rel="stylesheet" href="../css/home.css">
    </head>
    <body>
        <h1 style="color: White;">BENVINGUT <?php echo $_SESSION["username"] ?></h1>
        <a href="../php/logout.php" class="logout-btn">Logout</a>
    </body>
</html>
