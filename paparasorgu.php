<?php
/**
 * Papara No Sorgulama API - Direkt SQL Dosyasından
 * Telegram: @unutur
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// SQL dosyasını oku
$sql_file = __DIR__ . '/papara.sql';

if (!file_exists($sql_file)) {
    echo json_encode([
        'success' => false,
        'error' => 'papara.sql dosyası bulunamadı',
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$sql_content = file_get_contents($sql_file);

// Tüm INSERT sorgularını bul
preg_match_all("/INSERT INTO `papara` VALUES \((.+?)\);/", $sql_content, $matches);

$papara_list = [];
foreach ($matches[1] as $values) {
    $parcalar = explode(',', $values);
    if (count($parcalar) >= 4) {
        $papara_list[] = [
            'id' => trim($parcalar[0]),
            'paparano' => trim($parcalar[1]),
            'adsoyad' => trim($parcalar[2], " '"),
            'writer' => trim($parcalar[3], " '")
        ];
    }
}

// Papara no parametresi
$paparano = null;

if (isset($_GET['paparano'])) {
    $paparano = trim($_GET['paparano']);
} elseif (isset($_POST['paparano'])) {
    $paparano = trim($_POST['paparano']);
} else {
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['paparano'])) {
        $paparano = trim($input['paparano']);
    }
}

if (!$paparano) {
    echo json_encode([
        'success' => false,
        'error' => 'Papara no parametresi gerekli',
        'toplam_kayit' => count($papara_list),
        'kullanım' => '/paparasorgu.php?paparano=1354693996',
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Papara no'yu ara
$bulunan = null;
foreach ($papara_list as $kayit) {
    if ($kayit['paparano'] == $paparano) {
        $bulunan = $kayit;
        break;
    }
}

if ($bulunan) {
    echo json_encode([
        'success' => true,
        'paparano' => $bulunan['paparano'],
        'adsoyad' => $bulunan['adsoyad'],
        'writer' => $bulunan['writer'],
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Papara no kaydı bulunamadı',
        'aranan_papara' => $paparano,
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>