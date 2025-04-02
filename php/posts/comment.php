<?php
require_once '../conecta_db_persistent.php';
require_once '../comprobar_Login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Debes iniciar sesión para comentar.']);
        exit;
    }

    $idUsuari = $_SESSION['user_id'];
    $idPost = isset($_POST['idPost']) ? intval($_POST['idPost']) : 0;
    $comentario = trim($_POST['comentario']);

    if ($idPost <= 0 || empty($comentario)) {
        echo json_encode(['error' => 'Comentario no válido.']);
        exit;
    }

    try {
        $stmt = $db->prepare("INSERT INTO Comentari (idPost, idUsuari, comentari, dataComentari) VALUES (:idPost, :idUsuari, :comentario, NOW())");
        $stmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
        $stmt->bindParam(':idUsuari', $idUsuari, PDO::PARAM_INT);
        $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);
        $stmt->execute();

        echo json_encode(['success' => 'Comentario agregado.', 'comentario' => $comentario]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al agregar comentario: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Método no permitido.']);
}
?>
