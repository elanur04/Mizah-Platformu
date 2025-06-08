<?php if (!defined('DIRECT_ACCESS')) die('Direct access not permitted'); ?>

<div class="card" id="card-<?= $mizah['id'] ?>">
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
                <a href="profil.php?id=<?= $mizah['kullanici_id'] ?>" class="user-link">
                    <strong><?= htmlspecialchars($mizah['kullanici_adi']) ?></strong>
                </a>
                tarafından paylaşıldı
            </span>
            <span><?= htmlspecialchars($mizah['paylasim_tarihi']) ?></span>
        </div>

        <div class="vote-buttons">
            <?php $disabled = $mizah['user_voted'] > 0 ? 'disabled' : ''; ?>
            <button <?= $disabled ?> onclick="vote(this, true, <?= $mizah['id'] ?>)">Beğendim!💜</button>
            <button <?= $disabled ?> onclick="vote(this, false, <?= $mizah['id'] ?>)">Beğenmedim💔</button>
        </div>
        <div class="vote-count">
            Beğeni: <span id="up-<?= $mizah['id'] ?>"><?= $mizah['oy_up'] ?></span> | 
            Beğenmeme: <span id="down-<?= $mizah['id'] ?>"><?= $mizah['oy_down'] ?></span>
        </div>

        <div class="comments-section">
            <h4>Yorumlar</h4>
            <div class="comment-list" id="comments-<?= $mizah['id'] ?>">
                <p style="color:#555;">Yorumlar yükleniyor...</p>
            </div>
            <form class="comment-form" onsubmit="submitComment(event, <?= $mizah['id'] ?>)">
                <textarea name="yorum_metni" placeholder="Yorumunuzu buraya yazın..." required></textarea>
                <button type="submit">Yorum Yap</button>
            </form>
        </div>
    </div>
</div>

<style>
.user-link {
    color: #38044a;
    text-decoration: none;
    transition: color 0.2s;
}

.user-link:hover {
    color: #7129c9;
    text-decoration: underline;
}
</style> 