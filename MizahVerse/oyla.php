<?php
session_start();
require_once "db.php"; 

header('Content-Type: text/plain; charset=utf-8');


if (!isset($_SESSION["kullanici_id"])) {
    echo "Giriş yapmalısınız.";
    exit;
}

$icerik_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$oy_turu = isset($_POST['type']) ? $_POST['type'] : '';
$kullanici_id = $_SESSION['kullanici_id'];

if ($icerik_id > 0 && ($oy_turu === 'up' || $oy_turu === 'down')) {
    try {
       
        $stmt_check = $conn->prepare("SELECT COUNT(*) FROM begeni WHERE icerik_id = ? AND kullanici_id = ?");
        $stmt_check->execute([$icerik_id, $kullanici_id]);
        $oylanmis_mi = $stmt_check->fetchColumn();

        if ($oylanmis_mi > 0) {
            echo "Bu içeriği zaten oyladınız.";
            exit;
        }

        $sql_update = "";
        if ($oy_turu === 'up') {
            $sql_update = "UPDATE mizah_icerikleri SET oy_up = oy_up + 1 WHERE id = ?";
        } else { 
            $sql_update = "UPDATE mizah_icerikleri SET oy_down = oy_down + 1 WHERE id = ?";
        }

        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute([$icerik_id]);

        $stmt_insert_vote = $conn->prepare("INSERT INTO begeni (icerik_id, kullanici_id) VALUES (?, ?)");
        $stmt_insert_vote->execute([$icerik_id, $kullanici_id]);

        echo "Oy başarıyla kaydedildi."; 

    } catch (PDOException $e) {
  
        if ($e->getCode() == 23000) { 
            echo "Bu içeriği zaten oyladınız.";
        } else {
            error_log("Oy kaydedilirken bir hata oluştu: " . $e->getMessage());
            echo "Oy kaydedilirken bir hata oluştu.";
        }
    }
} else {
    echo "Geçersiz istek.";
}
?>