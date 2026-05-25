<?php
/**
 * Papara No Sorgulama API - Basit Dosya Okuma
 * Telegram: @unutur
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$sql_file = __DIR__ . '/papara.sql';

if (!file_exists($sql_file)) {
    echo json_encode([
        'success' => false,
        'error' => 'papara.sql dosyası bulunamadı',
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Dosyayı satır satır oku
$lines = file($sql_file);
$papara_list = [];

foreach ($lines as $line) {
    // INSERT satırını bul
    if (strpos($line, 'INSERT INTO `papara`') !== false) {
        // VALUES kısmını bul
        if (preg_match('/VALUES\s*\((.*?)\);/i', $line, $match)) {
            $values = $match[1];
            // Virgülle ayır
            $parcalar = explode(',', $values);
            
            if (count($parcalar) >= 3) {
                $paparano = trim($parcalar[1]);
                $paparano = trim($paparano, "' ");
                
                $adsoyad = trim($parcalar[2]);
                $adsoyad = trim($adsoyad, "' ");
                
                $writer = isset($parcalar[3]) ? trim($parcalar[3]) : '';
                $writer = trim($writer, "' ");
                
                $papara_list[$paparano] = [
                    'adsoyad' => $adsoyad,
                    'writer' => $writer
                ];
            }
        }
    }
}

// Parametre
$paparano = $_GET['paparano'] ?? $_POST['paparano'] ?? null;

if (!$paparano) {
    echo json_encode([
        'success' => false,
        'error' => 'Papara no parametresi gerekli',
        'kullanım' => '/paparasorgu.php?paparano=1422865344',
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($papara_list[$paparano])) {
    echo json_encode([
        'success' => true,
        'paparano' => $paparano,
        'adsoyad' => $papara_list[$paparano]['adsoyad'],
        'writer' => $papara_list[$paparano]['writer'],
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