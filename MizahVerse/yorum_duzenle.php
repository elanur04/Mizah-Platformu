<?php
session_start();
require_once "db.php"; 

header('Content-Type: text/plain; charset=utf-8');

if (!isset($_SESSION["kullanici_id"])) {
    echo "Hata: Giriş yapmalısınız.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $comment_id = filter_input(INPUT_POST, 'comment_id', FILTER_VALIDATE_INT);
    $new_yorum_metni = isset($_POST['new_yorum_metni']) ? htmlspecialchars($_POST['new_yorum_metni'], ENT_QUOTES, 'UTF-8') : '';

    if (!$comment_id || empty($new_yorum_metni)) {
        echo "Hata: Gerekli veriler eksik veya boş.";
        exit;
    }

    $kullanici_id = $_SESSION["kullanici_id"];

    try {
      
        $check_stmt = $conn->prepare("SELECT kullanici_id FROM yorumlar WHERE id = :comment_id");
        $check_stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
        $check_stmt->execute();
        $yorum_sahibi = $check_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$yorum_sahibi || $yorum_sahibi['kullanici_id'] != $kullanici_id) {
            echo "Hata: Bu yorumu düzenleme yetkiniz yok.";
            exit;
        }

        $stmt = $conn->prepare("UPDATE yorumlar SET yorum_metni = :yorum_metni WHERE id = :comment_id AND kullanici_id = :kullanici_id");
        $stmt->bindParam(':yorum_metni', $new_yorum_metni, PDO::PARAM_STR);
        $stmt->bindParam(':comment_id', $comment_id, PDO::PARAM_INT);
        $stmt->bindParam(':kullanici_id', $kullanici_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                echo "Başarılı: Yorum güncellendi.";
            } else {
                echo "Hata: Yorum bulunamadı veya değiştirilmedi.";
            }
        } else {
            echo "Hata: Yorum güncellenirken bir sorun oluştu (Execute hatası).";
        }
    } catch (PDOException $e) {
        echo "Veritabanı hatası: " . $e->getMessage();
    }
} else {
    echo "Hata: Geçersiz istek metodu.";
}
?>