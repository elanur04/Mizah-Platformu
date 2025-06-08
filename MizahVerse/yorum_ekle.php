<?php
session_start();
require_once "db.php";

header('Content-Type: text/plain; charset=utf-8'); 

if (!isset($_SESSION["kullanici_id"])) {
    echo "Hata: Giriş yapmalısınız.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $icerik_id = filter_input(INPUT_POST, 'icerik_id', FILTER_VALIDATE_INT);
 
    $yorum_metni = isset($_POST['yorum_metni']) ? htmlspecialchars($_POST['yorum_metni'], ENT_QUOTES, 'UTF-8') : '';

    if (!$icerik_id || empty($yorum_metni)) {
        echo "Hata: Gerekli veriler eksik veya boş.";
        exit;
    }

    $kullanici_id = $_SESSION["kullanici_id"];

    try {
        $stmt = $conn->prepare("INSERT INTO yorumlar (icerik_id, kullanici_id, yorum_metni) VALUES (:icerik_id, :kullanici_id, :yorum_metni)");
        $stmt->bindParam(':icerik_id', $icerik_id, PDO::PARAM_INT);
        $stmt->bindParam(':kullanici_id', $kullanici_id, PDO::PARAM_INT);
        $stmt->bindParam(':yorum_metni', $yorum_metni, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "Başarılı: Yorum eklendi.";
        } else {
            echo "Hata: Yorum eklenirken bir sorun oluştu (Execute hatası).";
        }
    } catch (PDOException $e) {
        echo "Veritabanı hatası: " . $e->getMessage();
    }
} else {
    echo "Hata: Geçersiz istek metodu.";
}
?>