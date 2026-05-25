<?php
/**
 * Papara No Sorgulama API - Düzeltilmiş
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

$sql_content = file_get_contents($sql_file);

// Tüm INSERT sorgularını bul (farklı formatlar için)
preg_match_all("/INSERT INTO `papara` VALUES \(([^;]+)\)/i", $sql_content, $matches);

$papara_list = [];

foreach ($matches[1] as $values) {
    // Değerleri temizle
    $values = trim($values);
    
    // Satır sonlarını temizle
    $values = preg_replace('/\s+/', ' ', $values);
    
    // Virgülle ayır
    $parcalar = array_map('trim', explode(',', $values));
    
    if (count($parcalar) >= 3) {
        // Tırnak işaretlerini temizle
        $paparano = trim($parcalar[1]);
        $paparano = trim($paparano, "'\"");
        
        $adsoyad = isset($parcalar[2]) ? trim($parcalar[2]) : '';
        $adsoyad = trim($adsoyad, "'\"");
        
        $writer = isset($parcalar[3]) ? trim($parcalar[3]) : '';
        $writer = trim($writer, "'\"");
        
        $papara_list[] = [
            'paparano' => $paparano,
            'adsoyad' => $adsoyad,
            'writer' => $writer
        ];
    }
}

// Parametre
$paparano = $_GET['paparano'] ?? $_POST['paparano'] ?? null;

if (!$paparano) {
    echo json_encode([
        'success' => false,
        'error' => 'Papara no parametresi gerekli',
        'toplam_kayit' => count($papara_list),
        'kullanım' => '/paparasorgu.php?paparano=1422865344',
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Ara (string olarak karşılaştır)
$bulunan = null;
foreach ($papara_list as $kayit) {
    if ((string)$kayit['paparano'] === (string)$paparano) {
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
        'toplam_kayit' => count($papara_list),
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>