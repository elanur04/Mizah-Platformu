<?php
session_start();
require_once "db.php"; 

$hata = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kullanici_adi = trim($_POST["kullanici_adi"]);
    $sifre = $_POST["sifre"];

    if (empty($kullanici_adi) || empty($sifre)) {
        $hata = "Kullanıcı adı ve şifre zorunludur.";
    } else {
     
        $stmt = $conn->prepare("SELECT * FROM kullanicilar WHERE kullanici_adi = :kullanici_adi");
        $stmt->execute(["kullanici_adi" => $kullanici_adi]);
        $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);


        if ($kullanici && password_verify($sifre, $kullanici["sifre"])) { 
            $_SESSION["kullanici_adi"] = $kullanici["kullanici_adi"];
            $_SESSION["kullanici_id"] = $kullanici["id"];
            header("Location: mizahVerse.php");
            exit;
        } else {
            $hata = "Kullanıcı adı veya şifre hatalı.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
  <meta charset="UTF-8">
  <title>Giriş Yap</title>
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
        padding: 35px;
        border-radius: 20px;
        backdrop-filter: blur(10px);
        width: 320px;
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
    input[type="password"]:focus {
        outline: none;
        border-color: #7129c9;
        box-shadow: 0 0 8px rgba(113, 41, 201, 0.3);
    }
    input[type="text"]::placeholder,
    input[type="password"]::placeholder {
        color: #666;
        opacity: 0.7;
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
    label {
        display: block;
        margin-top: 15px;
        color: black;
        font-size: 15px;
        font-weight: bold;
        margin-bottom: 5px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Giriş Yap</h2>

    <?php if ($hata): ?>
      <div class="error"><?= htmlspecialchars($hata) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <label for="kullanici_adi">Kullanıcı Adı</label>
      <input type="text" id="kullanici_adi" name="kullanici_adi" placeholder="Kullanıcı Adı" required>

      <label for="sifre">Şifre</label>
      <input type="password" id="sifre" name="sifre" placeholder="Şifre" required>

      <input type="submit" value="Giriş Yap">
    </form>

    <div class="alt-link">
      Hesabın yok mu? <a href="uyeol.php">Üye Ol</a>
    </div>
  </div>
</body>
</html>