<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header("Location: login.php");
    exit;
}

$kullanici_id = $_SESSION["kullanici_id"];


$aktif_sohbet = isset($_GET['kullanici']) ? intval($_GET['kullanici']) : null;


try {
    $stmt = $conn->prepare("
        SELECT DISTINCT 
            k.id,
            k.kullanici_adi,
            k.avatar_yolu,
            (SELECT mesaj 
             FROM mesajlar 
             WHERE (gonderen_id = k.id AND alici_id = :kullanici_id) 
                OR (gonderen_id = :kullanici_id AND alici_id = k.id) 
             ORDER BY gonderim_tarihi DESC 
             LIMIT 1) as son_mesaj,
            (SELECT gonderim_tarihi 
             FROM mesajlar 
             WHERE (gonderen_id = k.id AND alici_id = :kullanici_id) 
                OR (gonderen_id = :kullanici_id AND alici_id = k.id) 
             ORDER BY gonderim_tarihi DESC 
             LIMIT 1) as son_mesaj_tarihi,
            (SELECT COUNT(*) 
             FROM mesajlar 
             WHERE gonderen_id = k.id 
                AND alici_id = :kullanici_id 
                AND okundu = 0) as okunmamis_mesaj
        FROM kullanicilar k
        INNER JOIN mesajlar m 
        ON (m.gonderen_id = k.id AND m.alici_id = :kullanici_id)
           OR (m.gonderen_id = :kullanici_id AND m.alici_id = k.id)
        WHERE k.id != :kullanici_id
        GROUP BY k.id
        ORDER BY son_mesaj_tarihi DESC
    ");
    $stmt->bindParam(':kullanici_id', $kullanici_id);
    $stmt->execute();
    $sohbetler = $stmt->fetchAll(PDO::FETCH_ASSOC);

  
    if ($aktif_sohbet) {
       
        $stmt = $conn->prepare("SELECT id, kullanici_adi, avatar_yolu FROM kullanicilar WHERE id = ?");
        $stmt->execute([$aktif_sohbet]);
        $karsi_kullanici = $stmt->fetch(PDO::FETCH_ASSOC);

    
        $stmt = $conn->prepare("
            SELECT m.*, k.kullanici_adi, k.avatar_yolu
            FROM mesajlar m
            JOIN kullanicilar k ON m.gonderen_id = k.id
            WHERE (m.gonderen_id = :kullanici_id AND m.alici_id = :karsi_id)
               OR (m.gonderen_id = :karsi_id AND m.alici_id = :kullanici_id)
            ORDER BY m.gonderim_tarihi ASC
        ");
        $stmt->bindParam(':kullanici_id', $kullanici_id);
        $stmt->bindParam(':karsi_id', $aktif_sohbet);
        $stmt->execute();
        $mesajlar = $stmt->fetchAll(PDO::FETCH_ASSOC);

       
        $stmt = $conn->prepare("
            UPDATE mesajlar 
            SET okundu = 1 
            WHERE gonderen_id = :karsi_id 
              AND alici_id = :kullanici_id 
              AND okundu = 0
        ");
        $stmt->bindParam(':karsi_id', $aktif_sohbet);
        $stmt->bindParam(':kullanici_id', $kullanici_id);
        $stmt->execute();
    }
} catch(PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesajlar - MizahVerse</title>
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

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            display: flex;
            gap: 20px;
            height: calc(100vh - 80px);
        }

        .chat-list {
            width: 300px;
            background-color: rgba(142, 77, 196, 0.662);
            border-radius: 12px;
            padding: 15px;
            overflow-y: auto;
        }

        .chat-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
            background-color: rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .chat-item:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .chat-item.active {
            background-color: #7129c9;
        }

        .chat-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
            background-size: cover;
            background-position: center;
            background-color: #333;
        }

        .chat-info {
            flex-grow: 1;
        }

        .chat-name {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .chat-preview {
            font-size: 14px;
            color: #ccc;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .unread-badge {
            background-color: #7129c9;
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 10px;
        }

        .chat-window {
            flex-grow: 1;
            background-color: rgba(142, 77, 196, 0.662);
            border-radius: 12px;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
        }

        .chat-messages {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        .message {
            max-width: 70%;
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 15px;
            position: relative;
        }

        .message.sent {
            background-color: #7129c9;
            align-self: flex-end;
            border-bottom-right-radius: 5px;
        }

        .message.received {
            background-color: rgba(255, 255, 255, 0.1);
            align-self: flex-start;
            border-bottom-left-radius: 5px;
        }

        .message-time {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.6);
            margin-top: 5px;
        }

        .chat-input {
            padding: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            gap: 10px;
        }

        .chat-input textarea {
            flex-grow: 1;
            padding: 10px;
            border: none;
            border-radius: 20px;
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            resize: none;
            height: 40px;
            line-height: 20px;
        }

        .chat-input button {
            padding: 0 20px;
            border: none;
            border-radius: 20px;
            background-color: #7129c9;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .chat-input button:hover {
            background-color: #5a1ea8;
        }

        .no-chat-selected {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: rgba(255, 255, 255, 0.5);
            text-align: center;
            padding: 20px;
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

        .new-message-btn {
            display: block;
            padding: 12px;
            background-color: #7129c9;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .new-message-btn:hover {
            background-color: #5a1ea8;
        }

        .following-list {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(142, 77, 196, 0.95);
            padding: 20px;
            border-radius: 12px;
            z-index: 1000;
            max-width: 400px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .following-list h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: white;
        }

        .following-item {
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 8px;
            background-color: rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .following-item:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="chat-list">
            <a href="mizahVerse.php" class="back-button">← Ana Sayfaya Dön</a>
            <a href="#" class="new-message-btn" onclick="showFollowingList()">+ Yeni Mesaj</a>
            <?php foreach ($sohbetler as $sohbet): ?>
                <a href="?kullanici=<?= $sohbet['id'] ?>" 
                   class="chat-item <?= $aktif_sohbet == $sohbet['id'] ? 'active' : '' ?>">
                    <div class="chat-avatar" style="background-image: url('<?= htmlspecialchars($sohbet['avatar_yolu'] ?? 'uploads/profil/default_avatar.jpg') ?>')"></div>
                    <div class="chat-info">
                        <div class="chat-name">
                            <?= htmlspecialchars($sohbet['kullanici_adi']) ?>
                            <?php if ($sohbet['okunmamis_mesaj'] > 0): ?>
                                <span class="unread-badge"><?= $sohbet['okunmamis_mesaj'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="chat-preview"><?= htmlspecialchars(mb_substr($sohbet['son_mesaj'], 0, 30)) ?>...</div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="chat-window">
            <?php if ($aktif_sohbet && isset($karsi_kullanici)): ?>
                <div class="chat-header">
                    <div class="chat-avatar" style="background-image: url('<?= htmlspecialchars($karsi_kullanici['avatar_yolu'] ?? 'uploads/profil/default_avatar.jpg') ?>')"></div>
                    <div class="chat-name"><?= htmlspecialchars($karsi_kullanici['kullanici_adi']) ?></div>
                </div>
                <div class="chat-messages" id="chatMessages">
                    <?php foreach ($mesajlar as $mesaj): ?>
                        <div class="message <?= $mesaj['gonderen_id'] == $kullanici_id ? 'sent' : 'received' ?>">
                            <?= nl2br(htmlspecialchars($mesaj['mesaj'])) ?>
                            <div class="message-time">
                                <?= date('H:i', strtotime($mesaj['gonderim_tarihi'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="chat-input">
                    <textarea id="messageInput" placeholder="Mesajınızı yazın..." rows="1"></textarea>
                    <button onclick="sendMessage()">Gönder</button>
                </div>
            <?php else: ?>
                <div class="no-chat-selected">
                    <h2>Hoş Geldiniz!</h2>
                    <p>Mesajlaşmaya başlamak için soldaki listeden bir sohbet seçin.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="overlay" id="overlay"></div>
    <div class="following-list" id="followingList">
        <button class="close-btn" onclick="hideFollowingList()">&times;</button>
        <h3>Takip Ettikleriniz</h3>
        <?php
             try {
            $stmt = $conn->prepare("
                SELECT k.id, k.kullanici_adi, k.avatar_yolu
                FROM kullanicilar k
                INNER JOIN takipciler t ON k.id = t.takip_edilen_id
                WHERE t.takip_eden_id = :kullanici_id
                ORDER BY k.kullanici_adi ASC
            ");
            $stmt->bindParam(':kullanici_id', $kullanici_id);
            $stmt->execute();
            $takip_edilenler = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($takip_edilenler)) {
                echo '<p style="color: white;">Henüz kimseyi takip etmiyorsunuz.</p>';
            } else {
                foreach ($takip_edilenler as $kullanici) {
                    echo '
                    <div class="following-item" onclick="window.location.href=\'?kullanici=' . $kullanici['id'] . '\'">
                        <div class="chat-avatar" style="background-image: url(\'' . 
                        htmlspecialchars($kullanici['avatar_yolu'] ?? 'uploads/profil/default_avatar.jpg') . '\')"></div>
                        <div class="chat-name">' . htmlspecialchars($kullanici['kullanici_adi']) . '</div>
                    </div>';
                }
            }
        } catch(PDOException $e) {
            echo '<p style="color: red;">Kullanıcılar yüklenirken bir hata oluştu.</p>';
        }
        ?>
    </div>

    <script>
      
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message) return;

            fetch('mesaj_gonder.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `alici_id=<?= $aktif_sohbet ?>&mesaj=${encodeURIComponent(message)}`
            })
            .then(response => response.text())
            .then(data => {
                if (data === "success") {
                   
                    const chatMessages = document.getElementById('chatMessages');
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'message sent';
                    messageDiv.innerHTML = `
                        ${message.replace(/\n/g, '<br>')}
                        <div class="message-time">${new Date().toLocaleTimeString('tr-TR', {hour: '2-digit', minute:'2-digit'})}</div>
                    `;
                    chatMessages.appendChild(messageDiv);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                    input.value = '';
                } else {
                    alert('Mesaj gönderilemedi');
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                alert('Bir hata oluştu');
            });
        }

       
        document.getElementById('messageInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

         const chatMessages = document.getElementById('chatMessages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        <?php if ($aktif_sohbet): ?>
        setInterval(() => {
            fetch(`mesaj_kontrol.php?kullanici=${<?= $aktif_sohbet ?>}&son_mesaj=${encodeURIComponent(document.querySelector('.message:last-child')?.textContent || '')}`)
            .then(response => response.json())
            .then(data => {
                if (data.yeni_mesajlar) {
                    data.yeni_mesajlar.forEach(mesaj => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = 'message received';
                        messageDiv.innerHTML = `
                            ${mesaj.mesaj.replace(/\n/g, '<br>')}
                            <div class="message-time">${new Date(mesaj.gonderim_tarihi).toLocaleTimeString('tr-TR', {hour: '2-digit', minute:'2-digit'})}</div>
                        `;
                        chatMessages.appendChild(messageDiv);
                    });
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                }
            });
        }, 5000);
        <?php endif; ?>

        function showFollowingList() {
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('followingList').style.display = 'block';
        }

        function hideFollowingList() {
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('followingList').style.display = 'none';
        }

             document.getElementById('overlay').addEventListener('click', hideFollowingList);
    </script>
</body>
</html> 