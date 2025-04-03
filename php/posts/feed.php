<?php
require_once '../conecta_db_persistent.php';
header('Content-Type: application/json');

try {
    $stmt = $db->query("SELECT p.idPost, p.titol, p.descripcio, p.hasImg, 
                               (SELECT COUNT(*) FROM likeAPost WHERE idPost = p.idPost) AS likes
                        FROM Post p
                        ORDER BY p.DataCreacio DESC");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($posts as &$post) {
        $post['imagenes'] = [];
        $post['video'] = "";
        $post['audio'] = "";

        if ($post['hasImg'] == 1) {
            $stmtImg = $db->prepare("SELECT Foto FROM GaleriaPost WHERE idPost = :idPost");
            $stmtImg->bindParam(':idPost', $post['idPost'], PDO::PARAM_INT);
            $stmtImg->execute();
            $post['imagenes'] = $stmtImg->fetchAll(PDO::FETCH_COLUMN);

            if (file_exists("../../aud/uploads/videos/{$post['idPost']}.mp4")) {
                $post['video'] = "../../aud/uploads/videos/{$post['idPost']}.mp4";
            } elseif (file_exists("../../aud/uploads/audios/{$post['idPost']}.mp3")) {
                $post['audio'] = "../../aud/uploads/audios/{$post['idPost']}.mp3";
            }
        }
    }

    echo json_encode($posts);
} catch (PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
