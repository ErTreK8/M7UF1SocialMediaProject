<?php
require_once '../php/conecta_db_persistent.php';
require_once '../php/comprobar_Login.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?error=Debes iniciar sesión para editar tu perfil.");
    exit;
}

$user_id = $_SESSION['user_id'];

// Procesar la actualización del perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $lastname = trim($_POST['lastname']);
    $email = trim($_POST['email']);
    $tlf = trim($_POST['tlf']);
    $description = trim($_POST['description']);
    $ciudad = trim($_POST['ciudad']);
    $edad = trim($_POST['edad']);
    $fotoPerfil = null;

    if (isset($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/webp']; // Tipos permitidos
        $fileType = mime_content_type($_FILES['fotoPerfil']['tmp_name']);
    
        if (in_array($fileType, $allowedTypes)) {
            // Generar un nombre único para la imagen
            $extension = pathinfo($_FILES['fotoPerfil']['name'], PATHINFO_EXTENSION);
            $newFileName = 'perfil_' . $user_id . '_' . time() . '.' . $extension;
            $uploadPath = '../imgPerfil/' . $newFileName;
    
            // Mover el archivo a la carpeta de imágenes
            if (move_uploaded_file($_FILES['fotoPerfil']['tmp_name'], $uploadPath)) {
                $fotoPerfil = $uploadPath; // Guardamos la ruta en la base de datos
            } else {
                header("Location: editarDadesUsuari.php?error=Error al subir la imagen.");
                exit;
            }
        } else {
            header("Location: editarDadesUsuari.php?error=Formato de imagen no permitido. Usa JPG, PNG o WebP.");
            exit;
        }
    } else {
        $fotoPerfil = null; // No se subió imagen
    }

    if (!empty($name) && !empty($lastname) && !empty($email) && !empty($tlf) && !empty($edad)) {
        try {
            $updateQuery = $db->prepare("UPDATE Usuario 
                SET nom = :name, cognom = :lastname, email = :email, telefon = :tlf, descripcio = :description, 
                    idCiutat = :ciudad, edad = :edad" . ($fotoPerfil !== null ? ", fotoPerfil = :fotoPerfil" : "") . " 
                WHERE IdUsr = :user_id");

            $updateQuery->bindParam(':name', $name, PDO::PARAM_STR);
            $updateQuery->bindParam(':lastname', $lastname, PDO::PARAM_STR);
            $updateQuery->bindParam(':email', $email, PDO::PARAM_STR);
            $updateQuery->bindParam(':tlf', $tlf, PDO::PARAM_STR);
            $updateQuery->bindParam(':description', $description, PDO::PARAM_STR);
            $updateQuery->bindParam(':ciudad', $ciudad, PDO::PARAM_INT);
            $updateQuery->bindParam(':edad', $edad, PDO::PARAM_INT);
            $updateQuery->bindParam(':user_id', $user_id, PDO::PARAM_INT);

            if ($fotoPerfil !== null) {
                $updateQuery->bindParam(':fotoPerfil', $fotoPerfil, PDO::PARAM_STR);
            }

            $updateQuery->execute();


            header("Location: perfil.php?success=Perfil actualizado correctamente.");
            exit;
        } catch (PDOException $e) {
            header("Location: editarDadesUsuari.php?error=Error al actualizar usuario: " . urlencode($e->getMessage()));
            exit;
        }
    } else {
        header("Location: editarDadesUsuari.php?error=Todos los campos son obligatorios.");
        exit;
    }
}

// Obtener los datos del usuario para mostrar en el formulario
$query = $db->prepare("SELECT nom, cognom, email, telefon, descripcio, fotoPerfil, idCiutat, edad FROM Usuario WHERE IdUsr = :user_id");
$query->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$query->execute();
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: home.php?error=Usuario no encontrado.");
    exit;
}

// Obtener las ciudades
try {
    $ciudadesQuery = $db->query("SELECT idCiutat, nomCiutat FROM Ciutat ORDER BY nomCiutat ASC");
    $ciudades = $ciudadesQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ciudades = [];
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

    <?php if (isset($_GET['success'])): ?>
        <p class="message success"><?php echo htmlspecialchars($_GET['success']); ?></p>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <p class="message error"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>

    <div class="cajachula">
        

        <form action="editarDadesUsuari.php" method="POST" enctype="multipart/form-data">
        <div id="cajaFotoUsuari">
            <label for="fotoPerfilInput">
                <img src="<?php echo !empty($user['fotoPerfil']) ? htmlspecialchars($user['fotoPerfil']) : '../img/default-avatar.png'; ?>" 
                     alt="Foto de usuario" id="fotoPerfilPreview" 
                     style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; cursor: pointer;">
            </label>
            <input type="file" id="fotoPerfilInput" name="fotoPerfil" accept="image/*" style="display: none;">
            <br><br>
            </div>
            <label class="text" for="name">Nombre:</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
            <br><br>

            <label class="text" for="lastname">Apellido:</label>
            <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['cognom']); ?>" required>
            <br><br>

            <label class="text" for="email">Correo Electrónico:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <br><br>

            <label class="text" for="tlf">Teléfono:</label>
            <input type="text" id="tlf" name="tlf" value="<?php echo htmlspecialchars($user['telefon']); ?>" required>
            <br><br>

            <label class="text" for="edad">Edad:</label>
            <input type="number" id="edad" name="edad" value="<?php echo htmlspecialchars($user['edad']); ?>" required>
            <br><br>

            <label class="text" for="description">Descripción:</label>
            <textarea id="description" name="description"><?php echo htmlspecialchars($user['descripcio']); ?></textarea>
            <br><br>
            <label class="text" for="ciudad">Ciudad:</label>
            <select id="ciudad" name="ciudad" required>
                <?php foreach ($ciudades as $ciudad): ?>
                    <option value="<?php echo $ciudad['idCiutat']; ?>" <?php echo ($ciudad['idCiutat'] == $user['idCiutat']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($ciudad['nomCiutat']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <button class="logout-btn" type="submit">Guardar Cambios</button>
        </form>
    </div>

    <script>
        document.getElementById('fotoPerfilInput').addEventListener('change', function(event) {
            let reader = new FileReader();
            reader.onload = function(){
                let base64String = reader.result;
                document.getElementById('fotoPerfilPreview').src = base64String;
            }
            reader.readAsDataURL(event.target.files[0]);
        });
    </script>

</body>
</html>
