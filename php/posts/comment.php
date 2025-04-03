<?php
require_once '../conecta_db_persistent.php';
require_once '../comprobar_Login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Debes iniciar sesión para comentar.']);
        exit;
    }

    // Obtener datos del formulario
    $idUsuari = $_SESSION['user_id'];
    $idPost = isset($_POST['idPost']) ? intval($_POST['idPost']) : 0;
    $comentario = trim($_POST['comentario']);

    // Validar datos
    if ($idPost <= 0 || empty($comentario)) {
        echo json_encode(['error' => 'Comentario no válido.']);
        exit;
    }

    try {
        // Obtener el nombre del usuario
        $stmt = $db->prepare("SELECT nomUsari FROM Usuario WHERE IdUsr = :idUsuari");
        $stmt->bindParam(':idUsuari', $idUsuari, PDO::PARAM_INT);
        $stmt->execute();
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) {
            echo json_encode(['error' => 'Usuario no encontrado.']);
            exit;
        }

        $nomUsari = $usuario['nomUsari'];

        // Insertar el comentario en la base de datos
        $stmt = $db->prepare("INSERT INTO comentari (idPost, idUsuari, comentari, dataComentari) VALUES (:idPost, :idUsuari, :comentario, NOW())");
        $stmt->bindParam(':idPost', $idPost, PDO::PARAM_INT);
        $stmt->bindParam(':idUsuari', $idUsuari, PDO::PARAM_INT);
        $stmt->bindParam(':comentario', $comentario, PDO::PARAM_STR);
        $stmt->execute();

        // Respuesta exitosa
        echo json_encode([
            'success' => 'Comentario agregado.',
            'comentario' => $comentario,
            'nomUsari' => $nomUsari // Incluir el nombre del usuario
        ]);
    } catch (PDOException $e) {
        // Capturar errores
        echo json_encode(['error' => 'Error al agregar comentario: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Método no permitido.']);
}
?>