<?php
session_start();
require_once "db.php"; 

$hata = "";
$basari_mesaji = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = trim($_POST["kullanici_adi"]);
    $email = trim($_POST["email"]);
    $sifre = $_POST["sifre"];
    $sifre_tekrar = $_POST["sifre_tekrar"];

    if (empty($kullanici_adi) || empty($email) || empty($sifre) || empty($sifre_tekrar)) {
        $hata = "Tüm alanlar zorunludur.";
    } elseif ($sifre !== $sifre_tekrar) {
        $hata = "Şifreler uyuşmuyor.";
    } elseif (strlen($sifre) < 6) {
        $hata = "Şifre en az 6 karakter olmalıdır.";
    } else {
    
        $stmt = $conn->prepare("SELECT id FROM kullanicilar WHERE kullanici_adi = :kullanici_adi OR email = :email");
        $stmt->execute([":kullanici_adi" => $kullanici_adi, ":email" => $email]);
        if ($stmt->rowCount() > 0) {
            $hata = "Bu kullanıcı adı veya e-posta zaten kullanılıyor.";
        } else {
        
            $hashed_password = password_hash($sifre, PASSWORD_DEFAULT);

           
            $stmt = $conn->prepare("INSERT INTO kullanicilar (kullanici_adi, email, sifre) VALUES (:kullanici_adi, :email, :sifre)");
            if ($stmt->execute([":kullanici_adi" => $kullanici_adi, ":email" => $email, ":sifre" => $hashed_password])) {
                $basari_mesaji = "Kaydınız başarıyla tamamlandı! Şimdi giriş yapabilirsiniz.";
            } else {
                $hata = "Kayıt olurken bir hata oluştu. Lütfen tekrar deneyin.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Üye Ol</title>
  <link rel="icon" type="image/jpeg" href="1.jpg">
  <style>
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: 'Segoe UI', sans-serif;
        background: url('arka.jpg') no-repeat center center fixed;
        background-size: cover;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
    }
    .container {
        background-color: rgba(255, 255, 255, 0.9);
        padding: 40px;
        border-radius: 20px;
        backdrop-filter: blur(10px);
        width: 350px;
        color: black;
        box-shadow: 0 0 20px rgba(0,0,0,0.3);
        border: 2px solid rgba(113, 41, 201, 0.3);
    }
    h2 {
        text-align: center;
        margin-bottom: 20px;
        color: black;
        font-size: 28px;
        font-weight: bold;
    }
    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 12px;
        margin: 8px 0;
        background-color: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(113, 41, 201, 0.5);
        border-radius: 8px;
        color: black;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus {
        outline: none;
        border-color: #7129c9;
        box-shadow: 0 0 8px rgba(113, 41, 201, 0.3);
    }
    input[type="submit"] {
        width: 100%;
        padding: 12px;
        background-color: #6407ab;
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: bold;
        margin-top: 20px;
        cursor: pointer;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    input[type="submit"]:hover {
        background-color: #86c5e8;
        transform: translateY(-2px);
    }
    .alt-link {
        text-align: center;
        margin-top: 20px;
        font-size: 15px;
        color: black;
    }
    .alt-link a {
       color: #6407ab;
       text-decoration: underline;
       font-weight: bold;
    }
    .alt-link a:hover {
        color: #86c5e8;
    }
    .error {
        color: #ff3333;
        font-size: 14px;
        text-align: center;
        margin-top: 10px;
        background-color: rgba(255, 51, 51, 0.1);
        padding: 10px;
        border-radius: 5px;
        border: 1px solid rgba(255, 51, 51, 0.3);
    }
    .success {
        color: #00a65a;
        font-size: 14px;
        text-align: center;
        margin-top: 10px;
        background-color: rgba(0, 166, 90, 0.1);
        padding: 10px;
        border-radius: 5px;
        border: 1px solid rgba(0, 166, 90, 0.3);
    }
    label {
        display: block;
        margin-top: 15px;
        color: black;
        font-size: 15px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    ::placeholder {
        color: #666;
        opacity: 0.7;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Üye Ol</h2>

    <?php if ($hata): ?>
      <div class="error"><?= htmlspecialchars($hata) ?></div>
    <?php endif; ?>
    <?php if ($basari_mesaji): ?>
      <div class="success"><?= htmlspecialchars($basari_mesaji) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="kullanici_adi">Kullanıcı Adı</label>
      <input type="text" id="kullanici_adi" name="kullanici_adi" placeholder="Kullanıcı Adı" required>

      <label for="email">E-posta</label>
      <input type="email" id="email" name="email" placeholder="E-posta Adresi" required>

      <label for="sifre">Şifre</label>
      <input type="password" id="sifre" name="sifre" placeholder="Şifre" required>

      <label for="sifre_tekrar">Şifre Tekrar</label>
      <input type="password" id="sifre_tekrar" name="sifre_tekrar" placeholder="Şifre Tekrar" required>

      <input type="submit" value="Kayıt Ol">
    </form>

    <div class="alt-link">
      Zaten hesabın var mı? <a href="login.php">Giriş Yap</a>
    </div>
  </div>
</body>
</html>