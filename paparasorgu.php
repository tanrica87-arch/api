<?php
/**
 * Papara No Sorgulama API - SQL Dosyasından Tam Okuma
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

// Tüm INSERT sorgularını bul (daha geniş pattern)
preg_match_all("/INSERT INTO `papara` .*? VALUES \((.*?)\);/s", $sql_content, $matches);

$papara_list = [];

foreach ($matches[1] as $values) {
    // Parantez içindeki değerleri temizle
    $values = trim($values);
    
    // Basit virgül ayırma (tırnak içindeki virgülleri korumadan)
    $parcalar = array_map('trim', explode(',', $values));
    
    if (count($parcalar) >= 3) {
        // id'yi al (ilk değer)
        $id = trim($parcalar[0]);
        
        // paparano'yu al
        $paparano = isset($parcalar[1]) ? trim($parcalar[1]) : '';
        $paparano = trim($paparano, "'\"");
        
        // adsoyad'ı al
        $adsoyad = isset($parcalar[2]) ? trim($parcalar[2]) : '';
        $adsoyad = trim($adsoyad, "'\"");
        
        // writer'ı al
        $writer = isset($parcalar[3]) ? trim($parcalar[3]) : '';
        $writer = trim($writer, "'\"");
        
        if ($paparano && is_numeric($paparano)) {
            $papara_list[] = [
                'paparano' => $paparano,
                'adsoyad' => $adsoyad,
                'writer' => $writer
            ];
        }
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

// Ara
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