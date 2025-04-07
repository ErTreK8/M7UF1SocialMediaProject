<?php 
require_once '../php/comprobar_Login.php';
require_once '../php/carregarPerfil.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil</title>
    <link rel="stylesheet" href="../css/perfil.css">
</head>
<body>
    <main>
        <div class="cajachula">
            <a href="./editarDadesUsuari.php" id="botonEdicion">
                <p id="p-editar" class="text">Editar perfil</p>
                <img src="../img/circulo.png" width="30px">
            </a>
            <div id="cajaFotoUsuari">
                <img src="<?php echo !empty($user['fotoPerfil']) ? htmlspecialchars($user['fotoPerfil']) : '../imgPerfil/generic.png'; ?>" 
                     alt="Foto de usuario" id="fotoPerfilPreview" 
                     style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; cursor: pointer;">
                <p class="text"><?php echo htmlspecialchars($user['nomUsuari']); ?></p>
            </div>
            <p class="text"><strong>Nombre:</strong> <?php echo htmlspecialchars($user['nom']); ?></p>
            <p class="text"><strong>Apellido:</strong> <?php echo isset($user['cognoms']) ? htmlspecialchars($user['cognoms']) : 'No especificada'; ?></p>
            <p class="text">
                <?php echo (!empty($user['descripcio'])) ? htmlspecialchars($user['descripcio']) : "Descripción no especificada"; ?>
            </p>
            <p class="text"><strong>Edad:</strong> <?php echo isset($user['edat']) ? htmlspecialchars((new DateTime())->diff(new DateTime($user['edat']))->y) : 'No especificada';?></p>
            <?php
                if (!empty($user["Calle"]) || !empty($user["nomCiutat"])) {
                    echo '<p class="text"><strong>Ubicación:</strong> ' . 
                         (!empty($user["Calle"]) ? htmlspecialchars($user["Calle"]) . ", " : "") . 
                         (!empty($user["nomCiutat"]) ? htmlspecialchars($user["nomCiutat"]) : "Ciudad no especificada") . 
                         '</p>';
                } else {
                    echo '<p class="text">Ubicación no especificada</p>';
                }
            ?>
        </div>
    </main>
    <footer>
        <a href="../php/logout.php" class="logout-btn">Logout</a>
        <a href="../web/home.php" class="logout-btn">Home</a>
    </footer>
</body>
</html>