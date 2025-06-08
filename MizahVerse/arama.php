<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

$searchTerm = isset($_GET['q']) ? trim($_GET['q']) : '';

$arama_sonuclari = [];
if ($searchTerm !== '') {
    try {
        $stmt = $conn->prepare("SELECT mi.*, k.kullanici_adi,
                                     (SELECT COUNT(*) FROM begeni WHERE icerik_id = mi.id AND kullanici_id = :current_user_id) as user_voted,
                                     (mi.oy_up - mi.oy_down) as net_oy
                               FROM mizah_icerikleri mi
                               JOIN kullanicilar k ON mi.kullanici_id = k.id
                               WHERE mi.baslik LIKE :search_term 
                               OR mi.icerik LIKE :search_term
                               ORDER BY mi.paylasim_tarihi DESC");
        
        $searchPattern = "%{$searchTerm}%";
        $stmt->bindParam(':current_user_id', $_SESSION['kullanici_id'], PDO::PARAM_INT);
        $stmt->bindParam(':search_term', $searchPattern, PDO::PARAM_STR);
        $stmt->execute();
        $arama_sonuclari = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Veritabanı hatası: " . $e->getMessage();
        exit;
    }
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Arama Sonuçları - MizahVerse</title>
    <link rel="icon" type="image/jpeg" href="1.jpg">
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

        .container {
            max-width: 700px;
            margin: 30px auto;
            padding: 20px;
            background-color: rgba(142, 77, 196, 0.662);
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            border: 1px solid #cdcce2;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 8px;
        }

        .header h1 {
            margin: 0;
            color: #fff;
            font-size: 28px;
        }

        .search-term {
            color: #a958f2;
            font-weight: bold;
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
            position: relative;
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

        .media-container {
            margin: 15px auto;
            max-width: 500px;
            border-radius: 8px;
            overflow: hidden;
            background-color: rgba(0, 0, 0, 0.1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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

        .no-results {
            text-align: center;
            padding: 40px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="mizahVerse.php" class="back-button">← Geri Dön</a>
        
        <div class="header">
            <h1>Arama Sonuçları: <span class="search-term"><?php echo htmlspecialchars($searchTerm); ?></span></h1>
        </div>

        <?php if (empty($arama_sonuclari)): ?>
            <div class="no-results">
                <h2>Sonuç Bulunamadı</h2>
                <p>Aramanızla eşleşen içerik bulunamadı. Lütfen farklı anahtar kelimelerle tekrar deneyin.</p>
            </div>
        <?php else: ?>
            <?php foreach ($arama_sonuclari as $mizah): ?>
                <div class="card">
                    <div class="net-votes">
                        Net Beğeni: <?= $mizah['net_oy'] ?>
                    </div>
                    <h3><?= htmlspecialchars($mizah['baslik']) ?></h3>
                    
                    <?php
               
                    if (!empty($mizah['dosya_yolu'])) {
                        $dosya_uzantisi = strtolower(pathinfo($mizah['dosya_yolu'], PATHINFO_EXTENSION));
                        if (in_array($dosya_uzantisi, ['jpg', 'jpeg', 'png', 'gif'])) {
                            echo '<div class="media-container">';
                            echo '<img src="' . htmlspecialchars($mizah['dosya_yolu']) . '" alt="Yüklenen resim">';
                            echo '</div>';
                        } elseif ($dosya_uzantisi === 'mp4') {
                            echo '<div class="media-container">';
                            echo '<video controls>';
                            echo '<source src="' . htmlspecialchars($mizah['dosya_yolu']) . '" type="video/mp4">';
                            echo 'Tarayıcınız video elementini desteklemiyor.';
                            echo '</video>';
                            echo '</div>';
                        }
                    }

                    if (!empty($mizah['icerik'])) {
                        $embedded_video = embedVideo($mizah['icerik']);
                        if ($embedded_video) {
                            echo $embedded_video;
                        } else {
                            echo '<p>' . nl2br(htmlspecialchars($mizah['icerik'])) . '</p>';
                        }
                    }
                    ?>

                    <div class="post-interactions">
                        <div class="post-meta">
                            <span>
                                <strong><?= htmlspecialchars($mizah['kullanici_adi']) ?></strong>
                                tarafından paylaşıldı
                            </span>
                            <span><?= htmlspecialchars($mizah['paylasim_tarihi']) ?></span>
                        </div>

                        <div class="vote-buttons">
                            <?php
                            $disabled = $mizah['user_voted'] > 0 ? 'disabled' : '';
                            ?>
                            <button <?= $disabled ?> onclick="vote(this, true, <?= $mizah['id'] ?>)">Beğendim!💜</button>
                            <button <?= $disabled ?> onclick="vote(this, false, <?= $mizah['id'] ?>)">Beğenmedim💔</button>
                        </div>
                        <div class="vote-count">
                            Beğeni: <span id="up-<?= $mizah['id'] ?>"><?= $mizah['oy_up'] ?></span> | 
                            Beğenmeme: <span id="down-<?= $mizah['id'] ?>"><?= $mizah['oy_down'] ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    <script>
      
        function vote(button, liked, contentId) {
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
                    const card = button.closest('.card');
                    const upCountSpan = card.querySelector(`#up-${contentId}`);
                    const downCountSpan = card.querySelector(`#down-${contentId}`);
                    const netVotesDiv = card.querySelector('.net-votes');
                    
                    if (liked) {
                        upCountSpan.textContent = parseInt(upCountSpan.textContent) + 1;
                    } else {
                        downCountSpan.textContent = parseInt(downCountSpan.textContent) + 1;
                    }

                  
                    const netVotes = parseInt(upCountSpan.textContent) - parseInt(downCountSpan.textContent);
                    netVotesDiv.textContent = `Net Beğeni: ${netVotes}`;

                  
                    const buttons = card.querySelectorAll('.vote-buttons button');
                    buttons.forEach(btn => btn.disabled = true);
                } else {
                    alert(data);
                }
            })
            .catch(error => {
                console.error('Oy gönderme hatası:', error);
                alert('Oyunuzu göndermede bir sorun oluştu.');
            });
        }
    </script>
</body>
</html> 