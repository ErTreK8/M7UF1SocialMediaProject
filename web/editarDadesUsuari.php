<?php
session_start();
require_once '../php/conecta_db_persistent.php';
require_once '../php/comprobar_Login.php';
require_once '../php/carregarPerfil.php';

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
    $edad = trim($_POST['edad']);
    $email = trim($_POST['email']);
    $tlf = trim($_POST['tlf']);
    $description = trim($_POST['description']);
    $ciutat = trim($_POST['ciutat']);

    if (!empty($name) && !empty($lastname) && !empty($email) && !empty($tlf)) {
        try {
            $updateQuery = $db->prepare("UPDATE Usuario 
                SET nom = :name, cognom = :lastname, edad = :edad, email = :email, telefon = :tlf, descripcio = :description 
                WHERE IdUsr = :user_id");

            $updateQuery->bindParam(':name', $name, PDO::PARAM_STR);
            $updateQuery->bindParam(':lastname', $lastname, PDO::PARAM_STR);
            $updateQuery->bindParam(':email', $email, PDO::PARAM_STR);
            $updateQuery->bindParam(':edad', $edad, PDO::PARAM_INT);
            $updateQuery->bindParam(':tlf', $tlf, PDO::PARAM_STR);
            $updateQuery->bindParam(':description', $description, PDO::PARAM_STR);
            $updateQuery->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $updateQuery->execute();

            // Actualizar la sesión
            // $_SESSION['name'] = $name;
            // $_SESSION['lastname'] = $lastname;
            // $_SESSION['yearsold'] = $edad;
            // $_SESSION['email'] = $email;
            // $_SESSION['tlf'] = $tlf;
            // $_SESSION['description'] = $description;

            $_SESSION['success_message'] = "Perfil actualizado correctamente.";
            header("Location: perfil.php");
            exit;
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Error al actualizar usuario: " . $e->getMessage();
            header("Location: editarDadesUsuari.php");
            exit;
        }
    } else {
        $_SESSION['error_message'] = "Todos los campos son obligatorios.";
        header("Location: editarDadesUsuari.php");
        exit;
    }
}

// Obtener datos del usuario para mostrar en el formulario
$query = $db->prepare("SELECT edad ,nom, cognom, email, telefon, descripcio, fotoPerfil FROM Usuario WHERE IdUsr = :user_id");
$query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error_message'] = "Usuario no encontrado.";
    header("Location: home.php");
    exit;
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


        <form action="editarDadesUsuari.php" method="POST">
            <div id="cajaFotoUsuariEdit">
                <img src="..<?php echo !empty($user['fotoPerfil']) ? htmlspecialchars($user['fotoPerfil']) : '../img/default-avatar.png'; ?>" alt="Foto de usuario">
                <input type="file" id="fileInput" accept="image/*" style="display: none;" onchange="subirImagen()">
            </div>
            <label class="text" for="name">Nombre:</label>
            <br>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
            <br><br>

            <label class="text" for="lastname">Apellido:</label>
            <br>
            <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['cognom']); ?>" required>
            <br><br>
            <label class="text" for="edad">Edat:</label>
            <br>
            <input type="text" id="edad" name="edad" value="<?php echo htmlspecialchars($user['edad']); ?>" required>
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

            <label class="text" for="ciutat">Ciutat:</label>
            <br>
            <select id="miCombo" name="miCombo">
                <option value="">-- Selecciona --</option>
                <?php foreach ($ciutats as $row): ?>
                    <option value="<?php echo htmlspecialchars($row["idCiutat"]); ?>" 
                        <?php echo ($row["nomCiutat"] == $_SESSION['ciutat']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row["nomCiutat"]); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <button class="logout-btn" type="submit">Guardar Cambios</button>
        </form>
    </div>

</body>
</html>
