<?php
/**
 * Sicil Sorgulama API - Raw JSON Parser (Format Bozuk Olsa da Çalışır)
 * Telegram: @zahettim
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$json_file = __DIR__ . '/sicil_data.json';

if (!file_exists($json_file)) {
    echo json_encode([
        'success' => false,
        'error' => 'Dosya bulunamadı',
        'telegram' => '@zahettim'
    ]);
    exit;
}

$content = file_get_contents($json_file);

// Sicil parametresi
$sicil = $_GET['sicil'] ?? null;

if (!$sicil) {
    echo json_encode([
        'success' => false,
        'error' => 'Sicil numarası gerekli',
        'kullanım' => '/sicilsorgu.php?sicil=2925',
        'telegram' => '@zahettim'
    ]);
    exit;
}

// Bozuk JSON'dan veri çek (satır satır)
$lines = explode("\n", $content);
$bulunan = null;
$current_record = [];
$in_record = false;

foreach ($lines as $line) {
    $line = trim($line);
    
    // Yeni kayıt başlangıcı
    if (preg_match('/\{\s*$/', $line) || (strpos($line, '{') !== false && strpos($line, '}') === false)) {
        $in_record = true;
        $current_record = [];
        continue;
    }
    
    // Kayıt bitişi
    if ($in_record && (preg_match('/^\s*\}\s*,?\s*$/', $line) || strpos($line, '}') !== false)) {
        // Sicil kontrolü
        if (isset($current_record['AVUKAT_SICIL_NO']) && (string)$current_record['AVUKAT_SICIL_NO'] === (string)$sicil) {
            $bulunan = $current_record;
            break;
        }
        $in_record = false;
        $current_record = [];
        continue;
    }
    
    // Alanları parse et
    if ($in_record && preg_match('/"([^"]+)"\s*:\s*"([^"]*)"/', $line, $matches)) {
        $key = $matches[1];
        $value = $matches[2];
        $current_record[$key] = $value;
    }
    // Sayısal değerler için
    elseif ($in_record && preg_match('/"([^"]+)"\s*:\s*([0-9.]+)/', $line, $matches)) {
        $key = $matches[1];
        $value = $matches[2];
        $current_record[$key] = $value;
    }
    // Boolean değerler için
    elseif ($in_record && preg_match('/"([^"]+)"\s*:\s*(true|false)/', $line, $matches)) {
        $key = $matches[1];
        $value = $matches[2] === 'true' ? true : false;
        $current_record[$key] = $value;
    }
}

if ($bulunan) {
    // Boş olmayan alanları ekle
    $sonuc = ['success' => true];
    foreach ($bulunan as $key => $value) {
        if ($value !== '' && $value !== null) {
            $sonuc[$key] = $value;
        }
    }
    $sonuc['telegram'] = '@zahettim';
    echo json_encode($sonuc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Sicil numarası bulunamadı',
        'aranan_sicil' => $sicil,
        'telegram' => '@zahettim'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>