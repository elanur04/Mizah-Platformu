<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

$uploadDir = 'uploads/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$errors = [];

if (empty($_POST['baslik'])) {
    $errors[] = "Başlık boş olamaz!";
}

$dosya_yolu = null;
if (isset($_FILES['dosya']) && $_FILES['dosya']['error'] == 0) {
    $dosya = $_FILES['dosya'];
    $dosya_boyutu = $dosya['size'];
    $dosya_tipi = $dosya['type'];
    $dosya_tmp = $dosya['tmp_name'];
    $dosya_adi = uniqid() . '_' . basename($dosya['name']);
    
   
    if ($dosya_boyutu > 10 * 1024 * 1024) {
        $errors[] = "Dosya boyutu 10MB'dan küçük olmalıdır!";
    }
    

    $izin_verilen_tipler = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4'];
    if (!in_array($dosya_tipi, $izin_verilen_tipler)) {
        $errors[] = "Sadece JPG, PNG, GIF ve MP4 dosyaları yüklenebilir!";
    }
    

    if (empty($errors)) {
        if (move_uploaded_file($dosya_tmp, $uploadDir . $dosya_adi)) {
            $dosya_yolu = $uploadDir . $dosya_adi;
        } else {
            $errors[] = "Dosya yükleme hatası!";
        }
    }
}


if (empty($errors)) {
    try {
        $stmt = $conn->prepare("INSERT INTO mizah_icerikleri (kullanici_id, baslik, icerik, dosya_yolu, paylasim_tarihi) VALUES (:kullanici_id, :baslik, :icerik, :dosya_yolu, NOW())");
        
        $stmt->bindParam(':kullanici_id', $_SESSION['kullanici_id']);
        $stmt->bindParam(':baslik', $_POST['baslik']);
        $stmt->bindParam(':icerik', $_POST['icerik']);
        $stmt->bindParam(':dosya_yolu', $dosya_yolu);
        
        if ($stmt->execute()) {
            header("Location: mizahVerse.php");
            exit;
        } else {
            $errors[] = "Veritabanı hatası oluştu!";
        }
    } catch (PDOException $e) {
        $errors[] = "Veritabanı hatası: " . $e->getMessage();
    }
}

if (!empty($errors)) {
    echo "<div style='background-color: #ffebee; color: #c62828; padding: 15px; margin: 10px; border-radius: 5px;'>";
    foreach ($errors as $error) {
        echo "<p>$error</p>";
    }
    echo "<p><a href='mizahVerse.php' style='color: #2196f3; text-decoration: none;'>Geri Dön</a></p>";
    echo "</div>";
}
?>