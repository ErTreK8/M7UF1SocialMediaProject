<?php 
    // session_start();
    require_once '../php/carregarPerfil.php';
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Perfil</title>
        <link rel="stylesheet" href="../css/home.css">
    </head>
    <body>
        <main>
            <div class="cajachula">
                <a href="./editarDadesUsuari.php" id="botonEdicion">
                    <p class="text">Editar perfil</p>
                    <img src="../img/circulo.png" width="30px">
                </a>
                <div id="cajaFotoUsuari">
                    <img src="<?php echo '..' . $_SESSION['image'];?>" width="60px" height="60px" border-radius="50%" object-fit="cover">
                    <p class="text"><?php echo $_SESSION["username"] ?></p>
                </div>
                <p class="text"><?php echo (empty($_SESSION["description"])) ? "Descripció: " : $_SESSION["description"]; ?></p>
                <p class="text">Edat: <?php echo $_SESSION["yearsold"] ?></p>
                <p class="text">Ubicacio: <?php if(isset($_SESSION["comunitat"])) echo $_SESSION["comunitat"] + "/" + $_SESSION["comarca"] + "/" + $_SESSION["ciutat"] ?></p>
            </div>
        </main>
        <footer>
            <a href="../php/logout.php" class="logout-btn">Logout</a>
        </footer>
    </body>
</html>