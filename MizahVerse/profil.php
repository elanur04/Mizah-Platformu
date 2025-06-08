<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}


$kullanici_id = isset($_GET['id']) ? $_GET['id'] : $_SESSION['kullanici_id'];
try {
    $stmt = $conn->prepare("SELECT k.*, 
                                  (SELECT COUNT(*) FROM mizah_icerikleri WHERE kullanici_id = k.id) as paylasim_sayisi,
                                  (SELECT COUNT(*) FROM begeni WHERE kullanici_id = k.id) as begeni_sayisi
                           FROM kullanicilar k 
                           WHERE k.id = ?");
    $stmt->execute([$kullanici_id]);
    $kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT mi.*, 
                                  (SELECT COUNT(*) FROM begeni WHERE icerik_id = mi.id) as begeni_sayisi,
                                  (mi.oy_up - mi.oy_down) as net_oy
                           FROM mizah_icerikleri mi 
                           WHERE mi.kullanici_id = ? 
                           ORDER BY mi.paylasim_tarihi DESC");
    $stmt->execute([$kullanici_id]);
    $paylasimlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("SELECT 
        (SELECT COUNT(*) FROM takipciler WHERE takip_edilen_id = ?) as takipci_sayisi,
        (SELECT COUNT(*) FROM takipciler WHERE takip_eden_id = ?) as takip_edilen_sayisi,
        (SELECT COUNT(*) FROM takipciler WHERE takip_eden_id = ? AND takip_edilen_id = ?) as takip_durumu");
    $stmt->execute([$kullanici_id, $kullanici_id, $_SESSION['kullanici_id'], $kullanici_id]);
    $takip_bilgileri = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $kullanici_id == $_SESSION['kullanici_id']) {
    $uploadDir = 'uploads/profil/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }


    if (isset($_POST['biyografi'])) {
        $stmt = $conn->prepare("UPDATE kullanicilar SET biyografi = ? WHERE id = ?");
        $stmt->execute([trim($_POST['biyografi']), $_SESSION['kullanici_id']]);
    }


    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == 0) {
        $dosya = $_FILES['avatar'];
        $dosya_tipi = $dosya['type'];
        if (in_array($dosya_tipi, ['image/jpeg', 'image/png', 'image/gif'])) {
            $yeni_isim = uniqid('avatar_') . '_' . basename($dosya['name']);
            if (move_uploaded_file($dosya['tmp_name'], $uploadDir . $yeni_isim)) {
                $stmt = $conn->prepare("UPDATE kullanicilar SET avatar_yolu = ? WHERE id = ?");
                $stmt->execute([$uploadDir . $yeni_isim, $_SESSION['kullanici_id']]);
            }
        }
    }


    if (isset($_FILES['kapak_foto']) && $_FILES['kapak_foto']['error'] == 0) {
        $dosya = $_FILES['kapak_foto'];
        $dosya_tipi = $dosya['type'];
        if (in_array($dosya_tipi, ['image/jpeg', 'image/png', 'image/gif'])) {
            $yeni_isim = uniqid('kapak_') . '_' . basename($dosya['name']);
            if (move_uploaded_file($dosya['tmp_name'], $uploadDir . $yeni_isim)) {
                $stmt = $conn->prepare("UPDATE kullanicilar SET kapak_foto_yolu = ? WHERE id = ?");
                $stmt->execute([$uploadDir . $yeni_isim, $_SESSION['kullanici_id']]);
            }
        }
    }

    header("Location: profil.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($kullanici['kullanici_adi']) ?> - Profil</title>
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

        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .profile-header {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 20px;
            background-color: rgba(142, 98, 178, 0.66);
            box-shadow: 0 5px 15px rgba(142, 98, 178, 0.66);
        }

        .cover-photo {
            width: 100%;
            height: 300px;
            background-color: #2a2a2a;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .profile-info {
            padding: 20px;
            position: relative;
            background-color: rgba(0,0,0,0.5);
        }

        .avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid #fff;
            position: absolute;
            top: -75px;
            left: 30px;
            background-color: #333;
            background-size: cover;
            background-position: center;
        }

        .profile-details {
            margin-left: 200px;
            padding-top: 10px;
        }

        .username {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .bio {
            margin-bottom: 15px;
            font-style: italic;
            color: #ddd;
        }

        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .stat {
            background-color: rgba(0,0,0,0.3);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
        }

        .edit-profile {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #7129c9;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .edit-profile:hover {
            background-color: #5a1ea8;
        }

        .edit-form {
            display: none;
            background-color: rgba(0,0,0,0.8);
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #fff;
        }

        .form-group input[type="file"],
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #444;
            background-color: rgba(255,255,255,0.1);
            color: #fff;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .save-button {
            background-color: #7129c9;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .save-button:hover {
            background-color: #5a1ea8;
        }

        .posts-container {
            margin-top: 30px;
        }

        .post {
            background-color: rgba(203, 172, 214, 0.8);
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }

        .post-title {
            font-size: 18px;
            font-weight: bold;
            color: #38044a;
        }

        .post-date {
            font-size: 12px;
            color: #666;
        }

        .media-container {
            margin: 15px 0;
            max-width: 100%;
            border-radius: 8px;
            overflow: hidden;
        }

        .media-container img,
        .media-container video {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .post-stats {
            display: flex;
            gap: 15px;
            margin-top: 10px;
            color: #666;
            font-size: 14px;
        }

        .follow-button {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .follow-button.following {
            background-color: #7129c9;
            color: white;
        }

        .follow-button.not-following {
            background-color: transparent;
            color: white;
            border: 2px solid #7129c9;
        }

        .follow-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .back-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #7129c9;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #5a1ea8;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <a href="mizahVerse.php" class="back-button">← Ana Sayfaya Dön</a>
        <div class="profile-header">
            <div class="cover-photo" style="background-image: url('<?= htmlspecialchars($kullanici['kapak_foto_yolu'] ?? 'uploads/profil/default_cover.jpg') ?>')"></div>
            <div class="profile-info">
                <div class="avatar" style="background-image: url('<?= htmlspecialchars($kullanici['avatar_yolu'] ?? 'uploads/profil/default_avatar.jpg') ?>')"></div>
                <div class="profile-details">
                    <div class="username"><?= htmlspecialchars($kullanici['kullanici_adi']) ?></div>
                    <div class="bio"><?= nl2br(htmlspecialchars($kullanici['biyografi'] ?? 'Henüz bir biyografi eklenmemiş.')) ?></div>
                    <div class="stats">
                        <div class="stat">Paylaşım: <?= $kullanici['paylasim_sayisi'] ?></div>
                        <div class="stat">Beğeni: <?= $kullanici['begeni_sayisi'] ?></div>
                        <div class="stat">Takipçi: <?= $takip_bilgileri['takipci_sayisi'] ?></div>
                        <div class="stat">Takip: <?= $takip_bilgileri['takip_edilen_sayisi'] ?></div>
                    </div>
                </div>
                <?php if ($kullanici_id == $_SESSION['kullanici_id']): ?>
                    <button class="edit-profile" onclick="toggleEditForm()">Profili Düzenle</button>
                <?php elseif ($kullanici_id != $_SESSION['kullanici_id']): ?>
                    <button class="follow-button <?= $takip_bilgileri['takip_durumu'] ? 'following' : 'not-following' ?>" 
                            onclick="toggleFollow(<?= $kullanici_id ?>, this)" 
                            data-following="<?= $takip_bilgileri['takip_durumu'] ? 'true' : 'false' ?>">
                        <?= $takip_bilgileri['takip_durumu'] ? 'Takibi Bırak' : 'Takip Et' ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($kullanici_id == $_SESSION['kullanici_id']): ?>
            <div id="editForm" class="edit-form">
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="avatar">Profil Fotoğrafı:</label>
                        <input type="file" name="avatar" id="avatar" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="kapak_foto">Kapak Fotoğrafı:</label>
                        <input type="file" name="kapak_foto" id="kapak_foto" accept="image/*">
                    </div>
                    <div class="form-group">
                        <label for="biyografi">Biyografi:</label>
                        <textarea name="biyografi" id="biyografi"><?= htmlspecialchars($kullanici['biyografi'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="save-button">Kaydet</button>
                </form>
            </div>
        <?php endif; ?>

        <div class="posts-container">
            <h2>Paylaşımlar</h2>
            <?php foreach ($paylasimlar as $post): ?>
                <div class="post">
                    <div class="post-header">
                        <div class="post-title"><?= htmlspecialchars($post['baslik']) ?></div>
                        <div class="post-date"><?= htmlspecialchars($post['paylasim_tarihi']) ?></div>
                    </div>
                    <?php if (!empty($post['dosya_yolu'])): ?>
                        <div class="media-container">
                            <?php
                            $ext = strtolower(pathinfo($post['dosya_yolu'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])):
                            ?>
                                <img src="<?= htmlspecialchars($post['dosya_yolu']) ?>" alt="Paylaşılan görsel">
                            <?php elseif ($ext === 'mp4'): ?>
                                <video controls>
                                    <source src="<?= htmlspecialchars($post['dosya_yolu']) ?>" type="video/mp4">
                                    Tarayıcınız video elementini desteklemiyor.
                                </video>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($post['icerik'])): ?>
                        <div class="post-content"><?= nl2br(htmlspecialchars($post['icerik'])) ?></div>
                    <?php endif; ?>
                    <div class="post-stats">
                        <div>Beğeni: <?= $post['oy_up'] ?></div>
                        <div>Beğenmeme: <?= $post['oy_down'] ?></div>
                        <div>Net Beğeni: <?= $post['net_oy'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($paylasimlar)): ?>
                <p>Henüz paylaşım yapılmamış.</p>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function toggleEditForm() {
            const form = document.getElementById('editForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }

        function toggleFollow(userId, button) {
            const isFollowing = button.getAttribute('data-following') === 'true';
            const action = isFollowing ? 'takipten_cikar' : 'takip';

            fetch('takip_islem.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `takip_edilen_id=${userId}&islem=${action}`
            })
            .then(response => response.text())
            .then(data => {
                if (data.includes("Başarıyla") || data.includes("Takipten çıkarıldı")) {
                    const followingState = !isFollowing;
                    button.setAttribute('data-following', followingState.toString());
                    button.textContent = followingState ? 'Takibi Bırak' : 'Takip Et';
                    button.className = `follow-button ${followingState ? 'following' : 'not-following'}`;

                    const followersStat = document.querySelector('.stat:nth-child(3)');
                    const currentCount = parseInt(followersStat.textContent.split(': ')[1]);
                    followersStat.textContent = `Takipçi: ${followingState ? currentCount + 1 : currentCount - 1}`;
                } else {
                    alert(data);
                }
            })
            .catch(error => {
                console.error('Takip işlemi hatası:', error);
                alert('Bir hata oluştu');
            });
        }

        
        document.getElementById('avatar')?.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.avatar').style.backgroundImage = `url(${e.target.result})`;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        document.getElementById('kapak_foto')?.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.cover-photo').style.backgroundImage = `url(${e.target.result})`;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });
    </script>
</body>
</html> 