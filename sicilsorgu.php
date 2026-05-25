<?php
/**
 * Sicil Sorgulama API - Tüm Alanlar (Boşları gösterme)
 * Telegram: @zahettim
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// JSON dosyasını oku
$json_file = __DIR__ . '/sicil_data.json';

if (!file_exists($json_file)) {
    echo json_encode([
        'success' => false,
        'error' => 'Veritabanı dosyası bulunamadı',
        'telegram' => '@zahettim'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$content = file_get_contents($json_file);
$tum_veriler = json_decode($content, true);

if (!is_array($tum_veriler)) {
    echo json_encode([
        'success' => false,
        'error' => 'JSON formatı hatalı',
        'telegram' => '@zahettim'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Parametre
$sicil = $_GET['sicil'] ?? null;

if (!$sicil) {
    echo json_encode([
        'success' => false,
        'error' => 'Sicil numarası gerekli',
        'kullanım' => '/sicilsorgu.php?sicil=2925',
        'telegram' => '@zahettim'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Sicil ara
$bulunan = null;
foreach ($tum_veriler as $kayit) {
    if (isset($kayit['AVUKAT_SICIL_NO']) && (string)$kayit['AVUKAT_SICIL_NO'] === (string)$sicil) {
        $bulunan = $kayit;
        break;
    }
}

if ($bulunan) {
    $sonuc = ['success' => true];
    
    // Tüm alanları tara, boş veya null olmayanları ekle
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