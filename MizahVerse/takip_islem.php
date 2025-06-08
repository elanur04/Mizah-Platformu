<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"]) || !isset($_POST['takip_edilen_id'])) {
    http_response_code(400);
    echo "Geçersiz istek";
    exit;
}

$takip_eden_id = $_SESSION["kullanici_id"];
$takip_edilen_id = $_POST["takip_edilen_id"];
$islem = $_POST["islem"] ?? "takip";


if ($takip_eden_id == $takip_edilen_id) {
    http_response_code(400);
    echo "Kendinizi takip edemezsiniz";
    exit;
}

try {
    if ($islem === "takip") {
       
        $stmt = $conn->prepare("INSERT INTO takipciler (takip_eden_id, takip_edilen_id) VALUES (?, ?)");
        $stmt->execute([$takip_eden_id, $takip_edilen_id]);
        echo "Başarıyla takip edildi";
    } else {
       
        $stmt = $conn->prepare("DELETE FROM takipciler WHERE takip_eden_id = ? AND takip_edilen_id = ?");
        $stmt->execute([$takip_eden_id, $takip_edilen_id]);
        echo "Takipten çıkarıldı";
    }
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { 
        http_response_code(400);
        echo "Bu kullanıcıyı zaten takip ediyorsunuz";
    } else {
        http_response_code(500);
        echo "Bir hata oluştu";
    }
    exit;
} 