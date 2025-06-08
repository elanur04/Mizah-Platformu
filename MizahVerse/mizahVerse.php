<?php
session_start();
require_once "db.php"; 


if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}


$son_mizahlar = [];
try {
    $stmt = $conn->prepare("SELECT mi.*, k.kullanici_adi,
                                 (SELECT COUNT(*) FROM begeni WHERE icerik_id = mi.id AND kullanici_id = :current_user_id) as user_voted
                           FROM mizah_icerikleri mi
                           JOIN kullanicilar k ON mi.kullanici_id = k.id
                           ORDER BY mi.paylasim_tarihi DESC");
    $stmt->bindParam(':current_user_id', $_SESSION['kullanici_id'], PDO::PARAM_INT);
    $stmt->execute();
    $son_mizahlar = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
    exit; 
}

function embedVideo($url) {
   
    if (preg_match('/^(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=|embed\/|v\/|shorts\/|)([a-zA-Z0-9_-]{11})(?:\S+)?$/', $url, $matches)) {
        $videoId = $matches[1];
       
        return '<div class="video-container"><iframe src="http://www.youtube.com/embed/' . $videoId . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe></div>';
    }
    return null; 
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>MizahVerse</title>
    <link rel="icon" type="image/jpeg" href="1.jpg">
    <link rel="stylesheet" href="style.css">
    <style>
      
        body {
            background-image: url('arka.jpg');
            background-color: #0d0c22;
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #EAEAEA;
            line-height: 1.6;
        }

        header {
            background: linear-gradient(135deg, rgba(204, 177, 227, 0.66), rgba(113, 41, 201, 0.662));
            padding: 20px 15px;
            text-align: center;
            border-bottom: 3px solid rgba(113, 41, 201, 0.5);
        }

        header h1 {
            margin: 0;
            font-size: 44px;
            color: #f0f0f0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        input[type="radio"] {
            display: none;
        }

        nav {
            display: flex;
            justify-content: center;
            background-color: #3e4242;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        nav label {
            color: #EAEAEA;
            padding: 16px 25px;
            cursor: pointer;
            user-select: none;
            display: block;
            font-weight: bold;
            transition: background-color 0.3s, color 0.3s;
            text-transform: uppercase;
            font-size: 15px;
            letter-spacing: 0.5px;
        }

        nav label:hover {
            background-color: #0fa4d1;
            color: #ffffff;
        }

        #home:checked ~ nav label[for="home"],
        #upload:checked ~ nav label[for="upload"],
        #top:checked ~ nav label[for="top"],
        #following:checked ~ nav label[for="following"] {
            background-color: #7f58ad;
            color: #ffffff;
        }

        .container {
            max-width: 700px;
            margin: 30px auto;
            padding: 20px;
            background-color: rgba(142, 77, 196, 0.662);
            border-radius: 12px;
            display: none;
            color: #DCDCDC;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            border: 1px solid #cdcce2;
        }

        .container h2 {
            color: #24073e;
            font-size: 28px;
            margin-top: 0;
            margin-bottom: 25px;
            padding-bottom: 10px;
            border-bottom: 2px solid #a958f2;
            font-weight: 700;
        }

        #home:checked ~ #homeContent,
        #upload:checked ~ #uploadContent,
        #top:checked ~ #topContent,
        #following:checked ~ #followingContent {
            display: block;
        }

        .card {
            background-color: rgba(203, 172, 214, 0.8);
            margin: 0 auto 20px auto;
            padding: 20px;
            border-radius: 12px;
            color: #000000;
            border: 1px solid #ffffff;
            box-shadow: 0 3px 8px rgba(0,0,0,0.2);
            max-width: 600px; 
            transition: transform 0.2s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }

        .card h3 {
            color: #38044a;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .card p {
            font-size: 16px;
            margin-bottom: 15px;
        }

        .video-container {
            position: relative;
            padding-bottom: 42.86%;
            height: 0;
            overflow: hidden;
            margin-bottom: 15px;
            border-radius: 5px;
        }

        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }

        .post-interactions {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }

        .post-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 10px 0;
            font-size: 14px;
            color: #555;
        }

        .vote-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .vote-buttons button {
            flex: 1;
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            background-color: #7129c9;
            color: white;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .vote-buttons button:hover:not(:disabled) {
            background-color: #5a1ea8;
            transform: translateY(-2px);
        }

        .vote-count {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-top: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #220440;
            font-size: 16px;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ffffff;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 16px;
            background-color: rgba(187, 186, 200, 0.9);
            color: #000000;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="text"]:focus,
        textarea:focus {
            outline: none;
            border-color: #7a4ca2;
            box-shadow: 0 0 8px rgba(169, 88, 242, 0.4);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        #uploadContent button[type="submit"] {
            background-color: #6407ab;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: background-color 0.3s, transform 0.1s;
            display: inline-block;
            margin-top: 10px;
        }

        #uploadContent button[type="submit"]:hover {
            background-color: #86c5e8;
            transform: translateY(-2px);
        }
        #uploadContent button[type="submit"]:active {
            transform: translateY(0);
        }
        .logout-link {
            color: #EAEAEA;
            padding: 16px 25px;
            text-decoration: none;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 15px;
            letter-spacing: 0.5px;
            background-color: #3e4242;
            transition: background-color 0.3s, color 0.3s;
            display: block;
        }

        .logout-link:hover {
            background-color: #c0392b;
            color: #ffffff;
        }

       
        .comments-section {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        .comments-section h4 {
            color: #38044a;
            margin-bottom: 15px;
            font-size: 18px;
        }
        .comment-form textarea {
            width: calc(100% - 22px);
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: rgba(220, 220, 220, 0.9);
            color: #333;
            font-size: 14px;
            resize: vertical;
            min-height: 60px;
        }
        .comment-form button {
            background-color: #5d259c;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.2s;
        }
        .comment-form button:hover {
            background-color: #7b4eb2;
        }
        .comment {
            background-color: rgba(255, 255, 255, 0.7);
            padding: 10px 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            border: 1px solid #eee;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            color: #333;
            position: relative; 
        }
        .comment .comment-author {
            font-weight: bold;
            color: #333;
            font-size: 15px;
        }
        .comment .comment-date {
            font-size: 11px;
            color: #777;
            margin-left: 10px;
        }
        .comment p {
            margin: 5px 0 0;
            font-size: 14px;
            white-space: pre-wrap; 
        }

   
        .comment-actions {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            gap: 8px;
        }
        .comment-actions button {
            background: none;
            border: none;
            color: #5d259c;
            font-size: 12px;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            transition: all 0.2s ease;
        }
        .comment-actions button:hover {
            background-color: rgba(93, 37, 156, 0.1);
            color: #7b4eb2;
        }
        .comment-actions button.delete-btn {
            color: #e74c3c;
        }
        .comment-actions button.delete-btn:hover {
            background-color: rgba(231, 76, 60, 0.1);
            color: #c0392b;
        }

    
        .edit-comment-form {
            margin-top: 10px;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .edit-comment-form textarea {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            resize: vertical;
            min-height: 60px;
            box-sizing: border-box;
        }
        .edit-comment-form .button-group {
            display: flex;
            gap: 10px;
        }
        .edit-comment-form button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        .edit-comment-form button[type="submit"] {
            background-color: #5d259c;
            color: white;
        }
        .edit-comment-form button[type="submit"]:hover {
            background-color: #7b4eb2;
        }
        .edit-comment-form button.cancel-btn {
            background-color: #95a5a6;
            color: white;
        }
        .edit-comment-form button.cancel-btn:hover {
            background-color: #7f8c8d;
        }

        .search-container {
            display: flex;
            align-items: center;
            margin: 0 15px;
        }

        .search-container input {
            padding: 8px 15px;
            border: none;
            border-radius: 20px 0 0 20px;
            outline: none;
            width: 200px;
            background-color: rgba(255, 255, 255, 0.9);
            color: #333;
        }

        .search-container button {
            padding: 8px 15px;
            border: none;
            border-radius: 0 20px 20px 0;
            background-color: #7129c9;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .search-container button:hover {
            background-color: #5a1ea8;
        }

        .file-input {
            margin: 10px 0;
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 5px;
            width: 100%;
            box-sizing: border-box;
        }

        .file-preview {
            margin: 10px 0;
            max-width: 300px;
            max-height: 300px;
            overflow: hidden;
            border-radius: 5px;
            display: none;
        }

        .file-preview img, 
        .file-preview video {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .upload-info {
            margin: 15px 0;
            padding: 10px;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }

        .upload-info p {
            margin: 5px 0;
            font-size: 14px;
            color: #fff;
        }

        .media-container {
            margin: 15px auto;
            max-width: 500px;
            border-radius: 8px;
            overflow: hidden;
            background-color: rgba(0, 0, 0, 0.1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .media-container img {
            width: 100%;
            height: auto;
            max-height: 600px; 
            object-fit: contain; 
            display: block;
            margin: 0 auto;
            background-color: #f8f8f8;
        }

        .media-container video {
            width: 100%;
            max-height: 600px;
            display: block;
            object-fit: contain;
            background-color: #000;
        }

        .period-selector {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 20px;
            padding: 10px;
            background: rgba(0, 0, 0, 0.1);
            border-radius: 25px;
        }

        .period-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .period-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .period-btn.active {
            background: #7129c9;
            color: white;
        }

        .loading {
            text-align: center;
            padding: 20px;
            color: white;
            font-style: italic;
        }

        .rank-badge {
            position: absolute;
            top: -10px;
            left: -10px;
            background: #7129c9;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        #topContentList .card {
            position: relative;
            margin-top: 20px;
        }

        .net-votes {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
        }

        .nav-link {
            color: #EAEAEA;
            padding: 16px 25px;
            text-decoration: none;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 15px;
            letter-spacing: 0.5px;
            background-color: #3e4242;
            transition: background-color 0.3s, color 0.3s;
            display: block;
        }

        .nav-link:hover {
            background-color: #7129c9;
            color: #ffffff;
        }
    </style>
</head>
<body>

    <input type="radio" name="page" id="home" checked />
    <input type="radio" name="page" id="upload" />
    <input type="radio" name="page" id="top" />
    <input type="radio" name="page" id="following" />

    <header>
        <h1>MizahVerse</h1>
    </header>

    <nav>
        <label for="home">Son Mizahlar</label>
        <label for="upload">Mizah Yükle</label>
        <label for="top">En İyi İçerikler</label>
        <label for="following">Takip Edilenler</label>
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Mizah ara...">
            <button onclick="searchContent()">Ara</button>
        </div>
        <a href="profil.php" class="nav-link">Profilim</a>
        <a href="mesajlar.php" class="nav-link">Mesajlar</a>
        <a href="logout.php" class="logout-link">Çıkış Yap</a>
    </nav>

    <div id="homeContent" class="container">
        <h2>Son Mizahlar</h2>
        <?php if (empty($son_mizahlar)): ?>
            <p>Henüz mizah içeriği bulunmamaktadır. İlk mizahı siz yükleyin!</p>
        <?php else: ?>
            <?php 
            define('DIRECT_ACCESS', true);
            foreach ($son_mizahlar as $mizah) {
                include 'mizah_karti.php';
            }
            ?>
        <?php endif; ?>
    </div>

    <div id="uploadContent" class="container">
        <h2>İçerik Yükle</h2>
        <form method="POST" action="gonderi_ekle.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="baslik">Başlık:</label>
                <input type="text" name="baslik" id="baslik" required placeholder="Mizahınızın başlığını girin" />
            </div>
            <div class="form-group">
                <label for="icerik">Metin İçeriği veya YouTube Linki (İsteğe Bağlı):</label>
                <textarea name="icerik" id="icerik" rows="5" placeholder="Mizah içeriğinizi buraya yazın veya YouTube linki paylaşın"></textarea>
            </div>
            <div class="form-group">
                <label for="dosya">Resim/Video Yükle (İsteğe Bağlı):</label>
                <input type="file" name="dosya" id="dosya" accept="image/*,video/*" class="file-input" />
                <div class="file-preview" id="filePreview"></div>
            </div>
            <div class="upload-info">
                <p>Desteklenen dosya türleri: JPG, PNG, GIF, MP4</p>
                <p>Maksimum dosya boyutu: 10MB</p>
            </div>
            <button type="submit">Yükle</button>
        </form>
    </div>

    <div id="topContent" class="container">
        <h2>En İyi İçerikler</h2>
        <div class="period-selector">
            <button class="period-btn active" data-period="week">Bu Hafta</button>
            <button class="period-btn" data-period="month">Bu Ay</button>
            <button class="period-btn" data-period="all">Tüm Zamanlar</button>
        </div>
        <div id="topContentList">
            <div class="loading">Yükleniyor...</div>
        </div>
    </div>

    <div id="followingContent" class="container">
        <h2>Takip Ettiklerinizin Paylaşımları</h2>
        <?php
      
        try {
            $stmt = $conn->prepare("
                SELECT mi.*, k.kullanici_adi,
                       (SELECT COUNT(*) FROM begeni WHERE icerik_id = mi.id AND kullanici_id = :current_user_id) as user_voted
                FROM mizah_icerikleri mi
                JOIN kullanicilar k ON mi.kullanici_id = k.id
                JOIN takipciler t ON mi.kullanici_id = t.takip_edilen_id
                WHERE t.takip_eden_id = :current_user_id
                ORDER BY mi.paylasim_tarihi DESC
            ");
            $stmt->bindParam(':current_user_id', $_SESSION['kullanici_id'], PDO::PARAM_INT);
            $stmt->execute();
            $takip_edilen_mizahlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($takip_edilen_mizahlar)): ?>
                <p>Takip ettiğiniz kullanıcıların henüz paylaşımı bulunmamaktadır.</p>
            <?php else:
                if (!defined('DIRECT_ACCESS')) {
                    define('DIRECT_ACCESS', true);
                }
                foreach ($takip_edilen_mizahlar as $mizah) {
                    include 'mizah_karti.php';
                }
            endif;
            
        } catch (PDOException $e) {
            echo "Veritabanı hatası: " . $e->getMessage();
        }
        ?>
    </div>

<script>

    const currentUserId = <?= json_encode($_SESSION['kullanici_id']) ?>;
    const currentUsername = <?= json_encode($_SESSION['kullanici_adi']) ?>;

 
    function htmlspecialchars(str) {
        if (typeof str !== 'string') return str;
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return str.replace(/[&<>"']/g, function(m) { return map[m]; });
    }

    function nl2br(str, is_xhtml) {
        if (typeof str !== 'string') return str;
        var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
        return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
    }
 


    function vote(button, liked, contentId) {
        const cardContainer = button.closest('.card');
        if (!cardContainer) return;

        const upCountSpan = cardContainer.querySelector(`#up-${contentId}`);
        const downCountSpan = cardContainer.querySelector(`#down-${contentId}`);
        const allButtonsInGroup = button.parentElement.querySelectorAll('button');

        fetch('oyla.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${contentId}&type=${liked ? 'up' : 'down'}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes("Başarı")) {
                if (liked) {
                    upCountSpan.textContent = parseInt(upCountSpan.textContent) + 1;
                } else {
                    downCountSpan.textContent = parseInt(downCountSpan.textContent) + 1;
                }
                allButtonsInGroup.forEach(btn => {
                    btn.disabled = true;
                    if (liked) {
                        if (btn.innerText.includes('Beğendim')) btn.classList.add('voted-like');
                    } else {
                        if (btn.innerText.includes('Beğenmedim')) btn.classList.add('voted-dislike');
                    }
                });
            } else {
                alert(data);
                allButtonsInGroup.forEach(btn => btn.disabled = true);
            }
        })
        .catch(error => {
            console.error('Oy gönderme hatası:', error);
            alert('Oyunuzu göndermede bir sorun oluştu.');
        });
    }

 
    function submitComment(event, contentId) {
        event.preventDefault();

        const form = event.target;
        const textarea = form.querySelector('textarea[name="yorum_metni"]');
        const yorumMetni = textarea.value.trim();

        if (yorumMetni === "") {
            alert("Lütfen bir yorum yazın.");
            return;
        }

        fetch('yorum_ekle.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `icerik_id=${contentId}&yorum_metni=${encodeURIComponent(yorumMetni)}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes("Başarılı")) {
                textarea.value = '';
            
                fetchComments(contentId);
            } else {
                alert("Yorum gönderilirken bir hata oluştu: " + data);
            }
        })
        .catch(error => {
            console.error('Yorum gönderme hatası:', error);
            alert('Yorumunuzu göndermede bir sorun oluştu.');
        });
    }


    function addCommentToDisplay(contentId, comment) {
        const commentListDiv = document.getElementById(`comments-${contentId}`);
        const placeholder = commentListDiv.querySelector('p[style*="color:#555;"]');
        if (placeholder) {
            placeholder.remove();
        }

        const commentDiv = document.createElement('div');
        commentDiv.className = 'comment';
        commentDiv.id = `comment-${comment.id}`;

        let actionsHtml = '';
        if (comment.kullanici_id == currentUserId) {
            actionsHtml = `
                <div class="comment-actions">
                    <button onclick="editComment(${comment.id}, '${htmlspecialchars(comment.yorum_metni).replace(/'/g, "\\'").replace(/"/g, '\\"')}')">Düzenle</button>
                    <button class="delete-btn" onclick="deleteComment(${comment.id}, ${contentId})">Sil</button>
                </div>
            `;
        }

        commentDiv.innerHTML = `
            <span class="comment-author">${htmlspecialchars(comment.kullanici_adi)}</span>
            <span class="comment-date">${htmlspecialchars(comment.yorum_tarihi)}</span>
            ${actionsHtml}
            <p class="comment-text">${nl2br(htmlspecialchars(comment.yorum_metni))}</p>
        `;

        if (commentListDiv.firstChild) {
            commentListDiv.insertBefore(commentDiv, commentListDiv.firstChild);
        } else {
            commentListDiv.appendChild(commentDiv);
        }
    }


    function fetchComments(contentId) {
        const commentListDiv = document.getElementById(`comments-${contentId}`);
        commentListDiv.innerHTML = '<p style="color:#555;">Yorumlar yükleniyor...</p>';

        fetch(`yorum_getir.php?icerik_id=${contentId}`)
            .then(response => response.json())
            .then(comments => {
                commentListDiv.innerHTML = '';
                if (comments.length > 0) {
                    comments.forEach(comment => {
                        addCommentToDisplay(contentId, comment);
                    });
                } else {
                    commentListDiv.innerHTML = '<p style="color:#555;">Henüz yorum bulunmamaktadır. İlk yorumu siz yapın!</p>';
                }
            })
            .catch(error => {
                console.error('Yorumlar yüklenirken hata oluştu:', error);
                commentListDiv.innerHTML = '<p style="color:#f00;">Yorumlar yüklenirken bir hata oluştu.</p>';
            });
    }

    function editComment(commentId, currentText) {
        const commentDiv = document.getElementById(`comment-${commentId}`);
        if (!commentDiv) return;

        const commentTextElement = commentDiv.querySelector('.comment-text');
        const actionsDiv = commentDiv.querySelector('.comment-actions');


        if (commentDiv.querySelector('.edit-comment-form')) {
            return;
        }


        const editForm = document.createElement('form');
        editForm.className = 'edit-comment-form';
        editForm.innerHTML = `
            <textarea>${currentText}</textarea>
            <div class="button-group">
                <button type="submit">Kaydet</button>
                <button type="button" class="cancel-btn">İptal</button>
            </div>
        `;

      
        editForm.onsubmit = function(event) {
            event.preventDefault();
            const newText = editForm.querySelector('textarea').value.trim();
            if (newText === "") {
                alert("Yorum boş olamaz.");
                return;
            }
            sendEditRequest(commentId, newText);
        };

     
        editForm.querySelector('.cancel-btn').onclick = function() {
            commentTextElement.style.display = 'block';
            if (actionsDiv) actionsDiv.style.display = 'block';
            editForm.remove();
        };

   
        commentTextElement.style.display = 'none';
        if (actionsDiv) actionsDiv.style.display = 'none';
        commentDiv.appendChild(editForm);
    }

    function sendEditRequest(commentId, newText) {
        fetch('yorum_duzenle.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `comment_id=${commentId}&new_yorum_metni=${encodeURIComponent(newText)}`
        })
        .then(response => response.text())
        .then(data => {
            const commentDiv = document.getElementById(`comment-${commentId}`);
            if (!commentDiv) return;

            if (data.includes("Başarılı")) {
       
                const commentTextElement = commentDiv.querySelector('.comment-text');
                const actionsDiv = commentDiv.querySelector('.comment-actions');
                const editForm = commentDiv.querySelector('.edit-comment-form');

                commentTextElement.innerHTML = nl2br(htmlspecialchars(newText));
                commentTextElement.style.display = 'block';
                if (actionsDiv) actionsDiv.style.display = 'block';
                if (editForm) editForm.remove();
            } else {
                alert("Yorum güncellenirken bir hata oluştu: " + data);
            }
        })
        .catch(error => {
            console.error('Yorum düzenleme hatası:', error);
            alert('Yorumunuzu güncellerken bir sorun oluştu.');
        });
    }


    function deleteComment(commentId, contentId) {
        if (!confirm('Bu yorumu silmek istediğinizden emin misiniz?')) {
            return;
        }

        fetch('yorum_sil.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `comment_id=${commentId}`
        })
        .then(response => response.text())
        .then(data => {
            if (data.includes("Başarılı")) {
       
                const commentDiv = document.getElementById(`comment-${commentId}`);
                if (commentDiv) {
                    commentDiv.remove();
                }

                const commentListDiv = document.getElementById(`comments-${contentId}`);
                if (commentListDiv && !commentListDiv.hasChildNodes()) {
                    commentListDiv.innerHTML = '<p style="color:#555;">Henüz yorum bulunmamaktadır. İlk yorumu siz yapın!</p>';
                }
            } else {
                alert("Yorum silinirken bir hata oluştu: " + data);
            }
        })
        .catch(error => {
            console.error('Yorum silme hatası:', error);
            alert('Yorumunuzu silerken bir sorun oluştu.');
        });
    }


    document.addEventListener('DOMContentLoaded', function() {
        const topRadio = document.getElementById('top');
        const homeRadio = document.getElementById('home');

 
        document.querySelectorAll('.card').forEach(card => {
            const contentId = card.id.replace('card-', '');
            fetchComments(contentId);
        });


        topRadio.addEventListener('change', function() {
            if (this.checked) {
                fetchTopContent('week');
            }
        });

        function fetchTopContent(period = 'week') {
            const container = document.getElementById('topContentList');
            container.innerHTML = '<div class="loading">Yükleniyor...</div>';

            fetch(`en_iyi_icerikler.php?period=${period}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        container.innerHTML = `<div class="error">${data.error}</div>`;
                        return;
                    }

                    container.innerHTML = '';
                    data.forEach((mizah, index) => {
                        const card = document.createElement('div');
                        card.className = 'card';
                        
         
                        const rankBadge = document.createElement('div');
                        rankBadge.className = 'rank-badge';
                        rankBadge.textContent = index + 1;
                        
  
                        const netVotes = document.createElement('div');
                        netVotes.className = 'net-votes';
                        netVotes.textContent = `Net Beğeni: ${mizah.net_oy}`;

                        let content = `
                            <h3>${htmlspecialchars(mizah.baslik)}</h3>
                        `;


                        if (mizah.dosya_yolu) {
                            const ext = mizah.dosya_yolu.split('.').pop().toLowerCase();
                            if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) {
                                content += `
                                    <div class="media-container">
                                        <img src="${htmlspecialchars(mizah.dosya_yolu)}" alt="Yüklenen resim">
                                    </div>
                                `;
                            } else if (ext === 'mp4') {
                                content += `
                                    <div class="media-container">
                                        <video controls>
                                            <source src="${htmlspecialchars(mizah.dosya_yolu)}" type="video/mp4">
                                            Tarayıcınız video elementini desteklemiyor.
                                        </video>
                                    </div>
                                `;
                            }
                        }


                        if (mizah.icerik) {
                            content += `<p>${nl2br(htmlspecialchars(mizah.icerik))}</p>`;
                        }

                        content += `
                            <div class="post-meta">
                                <span><strong>${htmlspecialchars(mizah.kullanici_adi)}</strong> tarafından paylaşıldı</span>
                                <span>${htmlspecialchars(mizah.paylasim_tarihi)}</span>
                            </div>
                        `;

                        card.innerHTML = content;
                        card.appendChild(rankBadge);
                        card.appendChild(netVotes);
                        container.appendChild(card);
                    });

                    if (data.length === 0) {
                        container.innerHTML = '<div class="no-results">Bu dönemde henüz içerik bulunmamaktadır.</div>';
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    container.innerHTML = '<div class="error">İçerikler yüklenirken bir hata oluştu.</div>';
                });
        }


        const periodButtons = document.querySelectorAll('.period-btn');
        
       
        if (document.getElementById('top').checked) {
            fetchTopContent('week');
        }

        periodButtons.forEach(button => {
            button.addEventListener('click', function() {
                periodButtons.forEach(btn => btn.classList.remove('active'));
                this.classList.add('active');
                fetchTopContent(this.dataset.period);
            });
        });
    });

    function searchContent() {
        const searchTerm = document.getElementById('searchInput').value.trim();
        if (searchTerm === '') {
            alert('Lütfen bir arama terimi girin.');
            return;
        }


        window.location.href = `arama.php?q=${encodeURIComponent(searchTerm)}`;
    }

 
    document.getElementById('dosya').addEventListener('change', function(e) {
        const preview = document.getElementById('filePreview');
        preview.innerHTML = '';
        preview.style.display = 'none';

        const file = e.target.files[0];
        if (!file) return;


        if (file.size > 10 * 1024 * 1024) {
            alert('Dosya boyutu 10MB\'dan küçük olmalıdır!');
            this.value = '';
            return;
        }

        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            preview.appendChild(img);
            preview.style.display = 'block';
        } else if (file.type.startsWith('video/')) {
            const video = document.createElement('video');
            video.src = URL.createObjectURL(file);
            video.controls = true;
            preview.appendChild(video);
            preview.style.display = 'block';
        }
    });
</script>

</body>
</html>