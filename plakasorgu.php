<?php
/**
 * Plaka Sorgulama API - SQLite (Dosyadan)
 * Telegram: @zahettim
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// SQLite dosyası (plakasrg.sql dosyasını SQLite formatına çevirmen gerek)
// Not: .sql dosyasını .db ye çevir veya direkt SQLite kullan

$db_file = __DIR__ . '/plakalar.db';

// Eğer SQLite dosyası yoksa oluştur
if (!file_exists($db_file)) {
    // MySQL dump dosyasını oku
    $sql_file = __DIR__ . '/plakasrg.sql';
    if (file_exists($sql_file)) {
        $sql_content = file_get_contents($sql_file);
        
        // INSERT sorgularını bul
        preg_match_all("/INSERT INTO `75k_plaka` VALUES \((.+?)\);/", $sql_content, $matches);
        
        $db = new SQLite3($db_file);
        $db->exec("CREATE TABLE IF NOT EXISTS plakalar (
            id INTEGER PRIMARY KEY,
            plaka TEXT,
            isim TEXT,
            tarih TEXT,
            gsm TEXT
        )");
        
        foreach ($matches[1] as $values) {
            $parcalar = explode(',', $values);
            if (count($parcalar) >= 5) {
                $plaka = trim($parcalar[1], " '");
                $isim = trim($parcalar[2], " '");
                $tarih = trim($parcalar[3], " '");
                $gsm = trim($parcalar[4], " '");
                
                $db->exec("INSERT INTO plakalar (plaka, isim, tarih, gsm) VALUES ('$plaka', '$isim', '$tarih', '$gsm')");
            }
        }
    }
}

// SQLite'a bağlan
$db = new SQLite3($db_file);

// Plaka parametresi
$plaka = null;

if (isset($_GET['plaka'])) {
    $plaka = strtoupper(trim($_GET['plaka']));
} elseif (isset($_POST['plaka'])) {
    $plaka = strtoupper(trim($_POST['plaka']));
} else {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['plaka'])) {
        $plaka = strtoupper(trim($input['plaka']));
    }
}

if (!$plaka) {
    echo json_encode([
        'success' => false,
        'error' => 'Plaka parametresi gerekli',
        'kullanım' => '/?plaka=34KG4978',
        'telegram' => '@zahettim'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Sorgu
$stmt = $db->prepare("SELECT * FROM plakalar WHERE plaka = :plaka");
$stmt->bindValue(':plaka', $plaka, SQLITE3_TEXT);
$sonuc = $stmt->execute()->fetchArray(SQLITE3_ASSOC);

if ($sonuc) {
    echo json_encode([
        'success' => true,
        'plaka' => $sonuc['plaka'],
        'isim' => $sonuc['isim'],
        'tarih' => $sonuc['tarih'],
        'gsm' => $sonuc['gsm'],
        'telegram' => '@zahettim'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Plaka kaydı bulunamadı',
        'aranan_plaka' => $plaka,
        'telegram' => '@zahettim'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>