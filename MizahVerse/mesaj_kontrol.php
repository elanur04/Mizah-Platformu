<?php
session_start();
require_once "db.php";

header('Content-Type: application/json');

if (!isset($_SESSION["kullanici_id"]) || !isset($_GET['kullanici'])) {
    http_response_code(400);
    echo json_encode(["error" => "Geçersiz istek"]);
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];
$karsi_id = intval($_GET["kullanici"]);

try {

    $stmt = $conn->prepare("
        SELECT m.*, k.kullanici_adi 
        FROM mesajlar m
        JOIN kullanicilar k ON m.gonderen_id = k.id
        WHERE m.gonderen_id = :karsi_id 
          AND m.alici_id = :kullanici_id 
          AND m.okundu = 0
        ORDER BY m.gonderim_tarihi ASC
    ");
    $stmt->bindParam(':kullanici_id', $kullanici_id);
    $stmt->bindParam(':karsi_id', $karsi_id);
    $stmt->execute();
    $yeni_mesajlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($yeni_mesajlar)) {
        $stmt = $conn->prepare("
            UPDATE mesajlar 
            SET okundu = 1 
            WHERE gonderen_id = :karsi_id 
              AND alici_id = :kullanici_id 
              AND okundu = 0
        ");
        $stmt->bindParam(':karsi_id', $karsi_id);
        $stmt->bindParam(':kullanici_id', $kullanici_id);
        $stmt->execute();
    }

    echo json_encode(["yeni_mesajlar" => $yeni_mesajlar]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Bir hata oluştu"]);
    exit;
} 