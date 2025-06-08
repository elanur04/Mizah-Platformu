<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"]) || !isset($_POST['alici_id']) || !isset($_POST['mesaj'])) {
    http_response_code(400);
    echo "Geçersiz istek";
    exit;
}

$gonderen_id = $_SESSION["kullanici_id"];
$alici_id = intval($_POST["alici_id"]);
$mesaj = trim($_POST["mesaj"]);

if (empty($mesaj)) {
    http_response_code(400);
    echo "Mesaj boş olamaz";
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO mesajlar (gonderen_id, alici_id, mesaj) VALUES (?, ?, ?)");
    $stmt->execute([$gonderen_id, $alici_id, $mesaj]);
    echo "success";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Bir hata oluştu";
    exit;
} 