<?php
require_once '../../php/conecta_db_persistent.php';
require_once '../../php/comprobar_Login.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../web/login.php?error=Debes iniciar sesión.");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titol = trim($_POST['titol']);
    $descripcio = trim($_POST['descripcio']);
    $idUsuari = $_SESSION['user_id'];
    $tagsInput = trim($_POST['tags']); // Tags separados por comas (e.g., "arte, diseño, creatividad")

    if (empty($titol) || empty($descripcio)) {
        header("Location: ../../web/create_post.php?error=Todos los campos son obligatorios.");
        exit;
    }

    try {
        $db->beginTransaction();

        // Insertar post en la base de datos
        $stmt = $db->prepare("INSERT INTO Post (titol, descripcio, hashing, IdUsuari, DataCreacio) VALUES (:titol, :descripcio, 0, :idUsuari, NOW())");
        $stmt->bindParam(':titol', $titol, PDO::PARAM_STR);
        $stmt->bindParam(':descripcio', $descripcio, PDO::PARAM_STR);
        $stmt->bindParam(':idUsuari', $idUsuari, PDO::PARAM_INT);
        $stmt->execute();
        $idPost = $db->lastInsertId();

        $hasImg = 0;

        // Manejo de imágenes
        if (!empty($_FILES['imagenes']['name'][0])) {
            $targetDir = "../../aud/uploads/posts/$idPost/";
            $relativeTargetDir = "../aud/uploads/posts/$idPost/"; // Ruta relativa para la base de datos

            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true); // Crear directorio si no existe
            }

            foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmp_name) {
                $fileName = basename($_FILES['imagenes']['name'][$key]);
                $fileType = mime_content_type($tmp_name);

                if (in_array($fileType, ['image/jpeg', 'image/png', 'image/webp'])) {
                    $filePath = $targetDir . $fileName;
                    $relativeFilePath = $relativeTargetDir . $fileName; // Ruta relativa para la base de datos

                    if (move_uploaded_file($tmp_name, $filePath)) {
                        // Guardar la imagen en la tabla GaleriaPost
                        $stmt = $db->prepare("INSERT INTO GaleriaPost (idPost, Foto) VALUES (:idPost, :foto)");
                        $stmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
                        $stmt->bindParam(':foto', $relativeFilePath, PDO::PARAM_STR); // Guardar la ruta relativa
                        $stmt->execute();
                        $hasImg = 1;
                    }
                }
            }
        }

        // Manejo de video/audio
        if (!empty($_FILES['media']['name'])) {
            $mediaFileType = mime_content_type($_FILES['media']['tmp_name']);
            $targetDir = "../../aud/uploads/";
            $relativeTargetDir = "../aud/uploads/"; // Ruta relativa para la base de datos
            $fileExtension = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
            $filePath = $targetDir . ($mediaFileType == 'video/mp4' ? "videos/$idPost.mp4" : "audios/$idPost.mp3");
            $relativeFilePath = $relativeTargetDir . ($mediaFileType == 'video/mp4' ? "videos/$idPost.mp4" : "audios/$idPost.mp3"); // Ruta relativa para la base de datos

            // Verificar si el directorio existe y crearlo si no está presente
            if (!file_exists(dirname($filePath))) {
                mkdir(dirname($filePath), 0777, true); // Crear directorio si no existe
            }

            // Mover el archivo
            if (move_uploaded_file($_FILES['media']['tmp_name'], $filePath)) {
                // Guardar la ruta del video/audio en la tabla GaleriaPost
                $stmt = $db->prepare("INSERT INTO GaleriaPost (idPost, Foto) VALUES (:idPost, :foto)");
                $stmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
                $stmt->bindParam(':foto', $relativeFilePath, PDO::PARAM_STR); // Guardar la ruta relativa
                $stmt->execute();
                $hasImg = 1;
            } else {
                echo "<p>Error al mover el archivo.</p>";
                var_dump($_FILES['media']); // Depuración
                exit;
            }
        }

        // Procesar tags
        if (!empty($tagsInput)) {
            $tags = array_map('trim', explode(',', $tagsInput)); // Dividir tags por comas

            foreach ($tags as $tagName) {
                if (!empty($tagName)) {
                    // Verificar si el tag ya existe
                    $stmt = $db->prepare("SELECT idTag FROM tags WHERE tag = :tag");
                    $stmt->bindParam(':tag', $tagName, PDO::PARAM_STR);
                    $stmt->execute();
                    $tag = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (!$tag) {
                        // Crear el tag si no existe
                        $stmt = $db->prepare("INSERT INTO tags (tag, descripcio) VALUES (:tag, '')");
                        $stmt->bindParam(':tag', $tagName, PDO::PARAM_STR);
                        $stmt->execute();
                        $idTag = $db->lastInsertId();
                    } else {
                        $idTag = $tag['idTag'];
                    }

                    // Relacionar el tag con el post
                    $stmt = $db->prepare("INSERT INTO tagpost (idTag, idPost) VALUES (:idTag, :idPost)");
                    $stmt->bindParam(':idTag', $idTag, PDO::PARAM_INT);
                    $stmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
                    $stmt->execute();
                }
            }
        }

        // Actualizar el campo `hashing`
        $stmt = $db->prepare("UPDATE Post SET hashing = :hasImg WHERE idPost = :idPost");
        $stmt->bindParam(':hasImg', $hasImg, PDO::PARAM_INT);
        $stmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
        $stmt->execute();

        $db->commit();

        header("Location: ../../web/feed.php?success=Post publicado correctamente.");
        exit;
    } catch (PDOException $e) {
        $db->rollBack();
        header("Location: ../../web/create_post.php?error=Error al publicar: " . urlencode($e->getMessage()));
        exit;
    }
}
?>