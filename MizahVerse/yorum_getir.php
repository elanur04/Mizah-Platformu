<?php
session_start();
require_once "db.php"; 

header('Content-Type: application/json; charset=utf-8'); 

if (!isset($_SESSION["kullanici_id"])) {
    echo json_encode(["error" => "Giriş yapmalısınız."]);
    exit;
}

$icerik_id = filter_input(INPUT_GET, 'icerik_id', FILTER_VALIDATE_INT);

if (!$icerik_id) {
    echo json_encode(["error" => "Geçersiz içerik ID."]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT y.id, y.kullanici_id, y.yorum_metni, y.yorum_tarihi, k.kullanici_adi
                            FROM yorumlar y
                            JOIN kullanicilar k ON y.kullanici_id = k.id
                            WHERE y.icerik_id = :icerik_id
                            ORDER BY y.yorum_tarihi DESC"); 
    $stmt->bindParam(':icerik_id', $icerik_id, PDO::PARAM_INT);
    $stmt->execute();
    $yorumlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($yorumlar);

} catch (PDOException $e) {
    echo json_encode(["error" => "Veritabanı hatası: " . $e->getMessage()]);
}
?>