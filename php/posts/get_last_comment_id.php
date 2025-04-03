<?php
require_once '../conecta_db_persistent.php';
require_once '../comprobar_Login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Debes iniciar sesión para acceder a esta función.']);
        exit;
    }

    $idPost = isset($_POST['idPost']) ? intval($_POST['idPost']) : 0;

    if ($idPost <= 0) {
        echo json_encode(['error' => 'ID de post no válido.']);
        exit;
    }

    try {
        // Obtener el ID del último comentario para el post
        $stmt = $db->prepare("SELECT idComentari FROM comentari WHERE idPost = :idPost ORDER BY idComentari DESC LIMIT 1");
        $stmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode(['success' => true, 'idComentari' => $result['idComentari']]);
        } else {
            echo json_encode(['error' => 'No se encontró ningún comentario para este post.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al obtener el ID del comentario: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Método no permitido.']);
}
?>