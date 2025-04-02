<?php
require_once '../php/conecta_db_persistent.php';

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
$commentsQuery = $db->query("SELECT c.*, u.nomUsari FROM Comentari c JOIN Usuario u ON c.IdUsuari = u.IdUsr ORDER BY c.idComentari ASC");
$comments = $commentsQuery->fetchAll(PDO::FETCH_ASSOC);

// Organizar los comentarios por idPost
$commentsByPost = [];
foreach ($comments as $comment) {
    $postId = $comment['IdPost'];
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
</head>
<body>

<h1>Últimos Posts</h1>
<a href="./create_post.php">Crear Post</a>

<div class="posts-container">
    <?php foreach ($posts as $post): ?>
        <div class="post-card">
            <div class="post-header">
                <h3><?php echo htmlspecialchars($post['titol']); ?></h3>
            </div>
            <div class="post-content">
                <p><?php echo htmlspecialchars($post['descripcio']); ?></p>

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
                    <?php endif; ?>

                    <!-- Mostrar audio o video si está disponible -->
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
                </div>

                <!-- Sección de comentarios -->
                <div class="comments-section">
                    <h4>Comentarios:</h4>
                    <div class="comments" id="comments-<?php echo $post['idPost']; ?>">
                        <?php
                        if (isset($commentsByPost[$postId])) {
                            foreach ($commentsByPost[$postId] as $comment) {
                                echo '<div class="comment">';
                                echo '<strong>' . htmlspecialchars($comment['nomUsari']) . '</strong>: ';
                                echo htmlspecialchars($comment['comentari']);
                                echo '</div>';
                            }
                        } else {
                            echo '<p>Aún no hay comentarios.</p>';
                        }
                        ?>
                    </div>

                    <form class="comment-form" data-postid="<?php echo $post['idPost']; ?>">
                        <input type="text" name="comentario" placeholder="Escribe un comentario..." required>
                        <button type="submit">Comentar</button>
                    </form>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>