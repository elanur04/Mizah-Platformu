<?php
session_start();
require_once "db.php";

if (!isset($_SESSION["kullanici_id"])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Oturum açmanız gerekiyor']);
    exit;
}

function getTopContent($period = 'all') {
    global $conn;
    
    try {
        $where_clause = "";
        switch($period) {
            case 'week':
                $where_clause = "WHERE mi.paylasim_tarihi >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
                break;
            case 'month':
                $where_clause = "WHERE mi.paylasim_tarihi >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
                break;
            case 'all':
                $where_clause = "";
                break;
        }

        $sql = "SELECT 
                    mi.*, 
                    k.kullanici_adi,
                    (mi.oy_up - mi.oy_down) as net_oy,
                    (SELECT COUNT(*) FROM begeni WHERE icerik_id = mi.id AND kullanici_id = :current_user_id) as user_voted
                FROM mizah_icerikleri mi
                JOIN kullanicilar k ON mi.kullanici_id = k.id
                $where_clause
                ORDER BY net_oy DESC, mi.paylasim_tarihi DESC
                LIMIT 10";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':current_user_id', $_SESSION['kullanici_id'], PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return ['error' => $e->getMessage()];
    }
}

$period = isset($_GET['period']) ? $_GET['period'] : 'all';
$results = getTopContent($period);

header('Content-Type: application/json');
echo json_encode($results);
?> 