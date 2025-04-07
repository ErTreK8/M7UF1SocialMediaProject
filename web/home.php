<?php
    require_once '../php/comprobar_Login.php';
    require_once '../php/conecta_db_persistent.php';
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
        <a href="./perfil.php" class="logout-btn">Perfil</a>
        <a href="./feed.php" class="logout-btn">Feed</a>

    </body>
</html>
<?php

// Consulta para obtener todos los posts ordenados por fecha descendente
$postsQuery = $db->query("SELECT * FROM Post ORDER BY idPost DESC");
$posts = $postsQuery->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener las imágenes de los posts
$imagesQuery = $db->query("SELECT * FROM GaleriaPost");
$images = $imagesQuery->fetchAll(PDO::FETCH_ASSOC);

// Organizar las imágenes por idPost para facilitar su acceso
$imagesByPost = [];
foreach ($images as $image) {
    $postId = $image['idPost'];
    if (!isset($imagesByPost[$postId])) {
        $imagesByPost[$postId] = [];
    }
    $imagesByPost[$postId][] = $image['Foto']; // Guardar la URL de la imagen
}

// Consulta para obtener los comentarios de los posts
$commentsQuery = $db->query("SELECT c.*, u.nomUsuari FROM Comentari c JOIN Usuario u ON c.IdUsuari = u.idUsr ORDER BY c.idComentari ASC");
$comments = $commentsQuery->fetchAll(PDO::FETCH_ASSOC);

// Organizar los comentarios por idPost
$commentsByPost = [];
foreach ($comments as $comment) {
    $postId = $comment['idPost'];
    if (!isset($commentsByPost[$postId])) {
        $commentsByPost[$postId] = [];
    }
    $commentsByPost[$postId][] = $comment;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed</title>
    <link rel="stylesheet" href="../css/home.css">
    <link rel="stylesheet" href="../css/feed.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script src="../js/comments.js"></script>
</head>
<body>

<h1>Últimos Posts</h1>
<a href="./create_post.php">Crear Post</a>
<!-- Buscador -->
<div class="search-bar">
    <form method="GET" action="">
        <input type="text" name="q" placeholder="Buscar por título o tags..." value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>">
        <button type="submit">Buscar</button>
    </form>
</div>

<!-- Consulta para obtener los posts -->
<?php
$query = isset($_GET['q']) ? trim($_GET['q']) : '';
if (!empty($query)) {
    // Buscar posts por título o tags
    $stmt = $db->prepare("
        SELECT DISTINCT p.* 
        FROM Post p
        LEFT JOIN tagpost tp ON p.idPost = tp.idPost
        LEFT JOIN tags t ON tp.idTag = t.idTag
        WHERE p.titol LIKE :query OR t.tag LIKE :query
        ORDER BY p.idPost DESC
    ");
    $searchTerm = '%' . $query . '%';
    $stmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
} else {
    // Obtener todos los posts
    $stmt = $db->prepare("SELECT * FROM Post ORDER BY idPost DESC");
}
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="posts-container">
    <?php foreach ($posts as $post): ?>
        <div class="post-card">
            <div class="post-header">
                <h3><?php echo htmlspecialchars($post['titol']); ?></h3>
                <span class="post-date"><?php echo date('d/m/Y H:i', strtotime($post['DataCreacio'])); ?></span>
            </div>
            <div class="post-content">

                <!-- Sección de multimedia -->
                <div class="post-media">
                    <?php
                    $postId = $post['idPost'];

                    // Mostrar imágenes como carrousel
                    if (isset($imagesByPost[$postId]) && !empty($imagesByPost[$postId])): ?>
                        <div class="carousel">
                            <div class="slider-<?php echo $postId; ?>">
                                <?php foreach ($imagesByPost[$postId] as $image): ?>
                                    <?php if (strpos($image, '.mp4') === false && strpos($image, '.mp3') === false): ?>
                                        <div class="slide">
                                            <img src="<?php echo htmlspecialchars($image); ?>" alt="Imagen del post">
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <script>
                            $(document).ready(function () {
                                $('.slider-<?php echo $postId; ?>').slick({
                                    dots: false,
                                    infinite: true,
                                    speed: 300,
                                    slidesToShow: 1,
                                    slidesToScroll: 1,
                                    prevArrow: '<button type="button" class="slick-prev">Anterior</button>',
                                    nextArrow: '<button type="button" class="slick-next">Siguiente</button>'
                                });
                            });
                        </script>
                    <?php elseif (isset($post['hasImg']) && $post['hasImg'] === '1'): ?>
                        <p>No hay imágenes disponibles.</p>
                    <?php else: ?>
                        <p>No hay contenido multimedia disponible.</p>
                    <?php endif; ?>

                    <!-- Mostrar audio o video si está disponible -->
                    <?php if (isset($imagesByPost[$postId])): ?>
                        <?php foreach ($imagesByPost[$postId] as $image): ?>
                            <?php if (strpos($image, '.mp4') !== false): ?>
                                <video controls width="100%">
                                    <source src="<?php echo htmlspecialchars($image); ?>" type="video/mp4">
                                    Tu navegador no soporta la etiqueta de video.
                                </video>
                            <?php elseif (strpos($image, '.mp3') !== false): ?>
                                <audio controls>
                                    <source src="<?php echo htmlspecialchars($image); ?>" type="audio/mpeg">
                                    Tu navegador no soporta la etiqueta de audio.
                                </audio>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <p><?php echo htmlspecialchars($post['descripcio']); ?></p>
                <!-- Botón de Like para Posts -->
                <div class="like-section">
                    <form method="POST" action="../php/posts/like.php" style="display: inline;">
                        <input type="hidden" name="tipo" value="post">
                        <input type="hidden" name="idObjeto" value="<?php echo $post['idPost']; ?>">
                        <button type="submit" class="like-btn">
                            <?php
                            $postId = $post['idPost'];
                            $stmt = $db->prepare("SELECT COUNT(*) as totalLikes FROM likeapost WHERE idPost = :idPost");
                            $stmt->bindParam(':idPost', $postId, PDO::PARAM_INT);
                            $stmt->execute();
                            $totalLikes = $stmt->fetch(PDO::FETCH_ASSOC)['totalLikes'];

                            $stmt = $db->prepare("SELECT * FROM likeapost WHERE idPost = :idPost AND idUsuari = :idUsuari");
                            $stmt->bindParam(':idPost', $postId, PDO::PARAM_INT);
                            $stmt->bindParam(':idUsuari', $_SESSION['user_id'], PDO::PARAM_INT);
                            $stmt->execute();
                            $liked = $stmt->fetch(PDO::FETCH_ASSOC);

                            echo $liked ? '❤️ Quitar Like (' . $totalLikes . ')' : '🤍 Dar Like (' . $totalLikes . ')';
                            ?>
                        </button>
                    </form>
                </div>
                <hr style="border: 1px solid #ccc; margin: 20px 0;">
                <!-- Comentarios -->
                <div class="comments" id="comments-<?php echo $post['idPost']; ?>">
                    <?php
                    if (isset($commentsByPost[$postId])) {
                        foreach ($commentsByPost[$postId] as $comment): ?>
                            <div class="comment">
                                <strong><?php echo htmlspecialchars($comment['nomUsari']); ?>:</strong>
                                <?php echo htmlspecialchars($comment['comentari']); ?>
                                <span class="comment-date"><?php echo date('d/m/Y H:i', strtotime($comment['dataComentari'])); ?></span>

                                <!-- Botón de Like para Comentarios -->
                                <form method="POST" action="../php/posts/like.php" style="display: inline;">
                                    <input type="hidden" name="tipo" value="comentario">
                                    <input type="hidden" name="idObjeto" value="<?php echo $comment['idComentari']; ?>">
                                    <button type="submit" class="like-btn">
                                        <?php
                                        $stmt = $db->prepare("SELECT COUNT(*) as totalLikes FROM likeacomentari WHERE idComentari = :idComentari");
                                        $stmt->bindParam(':idComentari', $comment['idComentari'], PDO::PARAM_INT);
                                        $stmt->execute();
                                        $totalLikes = $stmt->fetch(PDO::FETCH_ASSOC)['totalLikes'];

                                        $stmt = $db->prepare("SELECT * FROM likeacomentari WHERE idComentari = :idComentari AND idUsuari = :idUsuari");
                                        $stmt->bindParam(':idComentari', $comment['idComentari'], PDO::PARAM_INT);
                                        $stmt->bindParam(':idUsuari', $_SESSION['user_id'], PDO::PARAM_INT);
                                        $stmt->execute();
                                        $liked = $stmt->fetch(PDO::FETCH_ASSOC);

                                        echo $liked ? '❤️ Quitar Like (' . $totalLikes . ')' : '🤍 Dar Like (' . $totalLikes . ')';
                                        ?>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach;
                    } else {
                        echo '<p>Aún no hay comentarios.</p>';
                    }
                    ?>
                </div>
                <div class="comment-form" data-postid="<?php echo $post['idPost']; ?>">
                    <form>
                        <input type="text" name="comentario" placeholder="Escribe un comentario..." required>
                        <button type="submit">Comentar</button>
                        <button type="submit">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>