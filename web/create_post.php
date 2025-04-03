<?php
require_once '../php/conecta_db_persistent.php';
require_once '../php/comprobar_Login.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?error=Debes iniciar sesión.");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Post</title>
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/crearPost.css">

    <script>
        // Función para mostrar/ocultar campos según la opción seleccionada
        function toggleMediaFields() {
            const selectedOption = document.querySelector('input[name="mediaType"]:checked').value;

            // Ocultar todos los campos de media
            document.getElementById('imageUpload').style.display = 'none';
            document.getElementById('videoAudioUpload').style.display = 'none';

            // Mostrar el campo correspondiente a la opción seleccionada
            if (selectedOption === 'images') {
                document.getElementById('imageUpload').style.display = 'block';
            } else if (selectedOption === 'videoAudio') {
                document.getElementById('videoAudioUpload').style.display = 'block';
            }
        }

        // Ejecutar la función al cargar la página
        window.onload = toggleMediaFields;
    </script>
</head>
<body>
        
<h1 class="blanco">Crear Nuevo Post</h1>

<?php if (isset($_GET['error'])): ?>
    <p class="message error"><?php echo htmlspecialchars($_GET['error']); ?></p>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
    <p class="message success"><?php echo htmlspecialchars($_GET['success']); ?></p>
<?php endif; ?>

<div class="cajachula">
    <form action="../php/posts/create.php" method="POST" enctype="multipart/form-data">
        <label class="text" for="titol">Título:</label>
        <input type="text" id="titol" name="titol" required>
        <br><br>

        <label class="text" for="descripcio">Descripción:</label>
        <textarea id="descripcio" name="descripcio" required></textarea>
        <br><br>

        <!-- Campo para tags -->
        <label class="text" for="tags">Tags (separados por comas):</label>
        <input type="text" id="tags" name="tags" placeholder="Ejemplo: arte, diseño, creatividad">
        <br><br>

        <!-- Opciones para seleccionar el tipo de contenido -->
        <label class="text">Selecciona el tipo de contenido:</label>
        <br>
        <input type="radio" id="optionImages" name="mediaType" value="images" checked onclick="toggleMediaFields()">
        <label for="optionImages">Imágenes</label>
        <input type="radio" id="optionVideoAudio" name="mediaType" value="videoAudio" onclick="toggleMediaFields()">
        <label for="optionVideoAudio">Video o Audio</label>
        <br><br>

        <!-- Campo para subir imágenes -->
        <div id="imageUpload">
            <label class="text">Subir imágenes (máx. 10):</label>
            <input type="file" id="imagenes" name="imagenes[]" accept="image/jpeg, image/png, image/webp" multiple>
            <br><br>
        </div>

        <!-- Campo para subir video o audio -->
        <div id="videoAudioUpload" style="display: none;">
            <label class="text">Subir video (MP4) o audio (MP3):</label>
            <input type="file" id="media" name="media" accept="video/mp4, audio/mp3">
            <br><br>
        </div>

        <button class="logout-btn" type="submit">Publicar</button>
    </form>
</div>

</body>
</html>