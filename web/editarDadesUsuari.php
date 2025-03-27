<?php
require_once '../php/conecta_db_persistent.php';
require_once '../php/comprobar_Login.php';

// Obtener ID de usuario desde la sesión
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = "Debes iniciar sesión para editar tu perfil.";
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Procesar actualización del perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $tlf = trim($_POST['tlf']);
    $description = trim($_POST['description']);
    $ciudad = trim($_POST['ciudad']);

    if (!empty($name) && !empty($lastname) && !empty($email) && !empty($tlf)) {
        try {
            $updateQuery = $db->prepare("UPDATE Usuario 
                SET nom = :name, cognom = :lastname, email = :email, telefon = :tlf, descripcio = :description, idCIutat = :ciudad 
                WHERE IdUsr = :user_id");

            $updateQuery->bindParam(':name', $name, PDO::PARAM_STR);
            $updateQuery->bindParam(':lastname', $lastname, PDO::PARAM_STR);
            $updateQuery->bindParam(':email', $email, PDO::PARAM_STR);
            $updateQuery->bindParam(':tlf', $tlf, PDO::PARAM_STR);
            $updateQuery->bindParam(':description', $description, PDO::PARAM_STR);
            $updateQuery->bindParam(':ciudad', $ciudad, PDO::PARAM_INT);
            $updateQuery->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $updateQuery->execute();

            // Actualizar la sesión
            $_SESSION['name'] = $name;
            $_SESSION['lastname'] = $lastname;
            $_SESSION['email'] = $email;
            $_SESSION['tlf'] = $tlf;
            $_SESSION['description'] = $description;
            $_SESSION['ciudad'] = $ciudad;

            $_SESSION['success_message'] = "Perfil actualizado correctamente.";
            header("Location: perfil.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al actualizar usuario: " . $e->getMessage();
            header("Location: edit_user.php");
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Todos los campos son obligatorios.";
        header("Location: edit_user.php");
        exit;
    }
}

// Obtener datos del usuario para mostrar en el formulario
$query = $db->prepare("SELECT nom, cognom, email, telefon, descripcio, fotoPerfil, idCIutat FROM Usuario WHERE IdUsr = :user_id");
$query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error_message'] = "Usuario no encontrado.";
    header("Location: home.php");
    exit;
}

// Obtener las ciudades ordenadas alfabéticamente
try {
    $ciudadesQuery = $db->query("SELECT idCIutat, nomCiutat FROM ciutat ORDER BY nomCiutat ASC");
    $ciudades = $ciudadesQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error al obtener las ciudades: " . $e->getMessage();
    $ciudades = []; // Si hay un error, inicializa $ciudades como un array vacío
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="../css/home.css">
</head>
<body>

    <h1 class="text">Editar Perfil</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <p class="message success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></p>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <p class="message error"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></p>
    <?php endif; ?>

    <div class="cajachula">
        <div id="cajaFotoUsuari">
            <img src="<?php echo !empty($user['fotoPerfil']) ? htmlspecialchars($user['fotoPerfil']) : '../img/default-avatar.png'; ?>" alt="Foto de usuario">
        </div>

        <form action="editarDadesUsuari.php" method="POST">
            <label class="text" for="name">Nombre:</label>
            <br>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
            <br><br>

            <label class="text" for="lastname">Apellido:</label>
            <br>
            <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['cognom']); ?>" required>
            <br><br>

            <label class="text" for="email">Correo Electrónico:</label>
            <br>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <br><br>

            <label class="text" for="tlf">Teléfono:</label>
            <br>
            <input type="text" id="tlf" name="tlf" value="<?php echo htmlspecialchars($user['telefon']); ?>" required>
            <br><br>

            <label class="text" for="description">Descripción:</label>
            <br>
            <textarea id="description" name="description"><?php echo htmlspecialchars($user['descripcio']); ?></textarea>
            <br><br>

            <label class="text" for="ciudad">Ciudad:</label>
            <br>
            <select id="ciudad" name="ciudad" required>
                <?php if (!empty($ciudades)): ?>
                    <?php foreach ($ciudades as $ciudad): ?>
                        <option value="<?php echo $ciudad['idCIutat']; ?>" <?php echo ($ciudad['idCIutat'] == $user['idCIutat']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($ciudad['nomCiutat']); ?>
                        </option>
                    <?php endforeach; ?>
                <?php else: ?>
                    <option value="">No hay ciudades disponibles</option>
                <?php endif; ?>
            </select>
            <br><br>

            <button class="logout-btn" type="submit">Guardar Cambios</button>
        </form>
    </div>

</body>
</html>