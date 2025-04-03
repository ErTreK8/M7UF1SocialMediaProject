<?php
require_once '../conecta_db_persistent.php';
require_once '../comprobar_Login.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(['error' => 'Debes iniciar sesión para dar/quitar likes.']);
        exit;
    }

    $idUsuari = $_SESSION['user_id'];
    $tipo = $_POST['tipo']; // 'post' o 'comentario'
    $idObjeto = intval($_POST['idObjeto']); // idPost o idComentari

    try {
        if ($tipo === 'post') {
            $tabla = 'likeapost';
            $columna = 'idPost';
        } elseif ($tipo === 'comentario') {
            $tabla = 'likeacomentari';
            $columna = 'idComentari';
        } else {
            echo json_encode(['error' => 'Tipo de objeto no válido.']);
            exit;
        }

        // Verificar si ya existe un like
        $stmt = $db->prepare("SELECT * FROM $tabla WHERE $columna = :idObjeto AND idUsuari = :idUsuari");
        $stmt->bindParam(':idObjeto', $idObjeto, PDO::PARAM_INT);
        $stmt->bindParam(':idUsuari', $idUsuari, PDO::PARAM_INT);
        $stmt->execute();
        $likeExistente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($likeExistente) {
            // Quitar like
            $stmt = $db->prepare("DELETE FROM $tabla WHERE $columna = :idObjeto AND idUsuari = :idUsuari");
            $stmt->bindParam(':idObjeto', $idObjeto, PDO::PARAM_INT);
            $stmt->bindParam(':idUsuari', $idUsuari, PDO::PARAM_INT);
            $stmt->execute();

            header("Location: ../../web/feed.php"); // Redirigir al usuario
            exit;
        } else {
            // Dar like
            $stmt = $db->prepare("INSERT INTO $tabla ($columna, idUsuari) VALUES (:idObjeto, :idUsuari)");
            $stmt->bindParam(':idObjeto', $idObjeto, PDO::PARAM_INT);
            $stmt->bindParam(':idUsuari', $idUsuari, PDO::PARAM_INT);
            $stmt->execute();

            header("Location: ../../web/feed.php"); // Redirigir al usuario
            exit;
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al procesar el like: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Método no permitido.']);
}
?>