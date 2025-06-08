<?php
session_start();

$_SESSION = array();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

session_destroy();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Çıkış Yapılıyor</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('arka.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f1c2eb;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .logout-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            text-align: center;
            max-width: 400px;
            backdrop-filter: blur(10px);
        }
        .spinner {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            border: 6px solid #f3e5f5;
            border-top: 6px solid #9c27b0;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 {
            margin-bottom: 20px;
            color: #333;
            font-size: 24px;
        }
        p {
            color: #666;
            font-size: 16px;
            line-height: 1.5;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="spinner"></div>
        <h2>Çıkış yapılıyor...</h2>
        <p>Başarıyla çıkış yaptınız.<br>Giriş sayfasına yönlendiriliyorsunuz.</p>
    </div>

    <script>
        setTimeout(function() {
            window.location.href = "login.php";
        }, 2000);
    </script>
</body>
</html>