<?php
/**
 * TCPRO TC Kimlik Sorgulama API
 * Telegram: @unutur
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// JSON dosyasını oku
$json_file = __DIR__ . '/tcpro.json';

if (!file_exists($json_file)) {
    echo json_encode([
        'success' => false,
        'error' => 'Veritabanı dosyası bulunamadı',
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$content = file_get_contents($json_file);
$data = json_decode($content, true);

// TCPRO verilerini topla
$tcpro_listesi = [];

if (isset($data['data']['tcpro']['data'])) {
    $tcpro_listesi = $data['data']['tcpro']['data'];
} elseif (isset($data['data']['tcpro']) && is_array($data['data']['tcpro'])) {
    $tcpro_listesi = $data['data']['tcpro'];
}

// Parametre
$tc = $_GET['tc'] ?? null;

if (!$tc) {
    echo json_encode([
        'success' => false,
        'error' => 'TC kimlik numarası gerekli',
        'kullanım' => '/tcpro.php?tc=11111111356',
        'toplam_kayit' => count($tcpro_listesi),
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// TC ara
$bulunan = null;
foreach ($tcpro_listesi as $kayit) {
    if (isset($kayit['Tc']) && $kayit['Tc'] == $tc) {
        $bulunan = $kayit;
        break;
    }
}

if ($bulunan) {
    echo json_encode([
        'success' => true,
        'tc' => $bulunan['Tc'],
        'ad' => $bulunan['Ad'],
        'soyad' => $bulunan['Soyad'],
        'anne_ad' => $bulunan['AnneAd'],
        'baba_ad' => $bulunan['BabaAd'],
        'dogum_tarihi' => $bulunan['DogumTarihi'],
        'dogum_yeri' => $bulunan['DogumYer'],
        'cinsiyet' => $bulunan['Cinsiyet'],
        'medeni' => $bulunan['Medeni'],
        'cilt_no' => $bulunan['CiltNumarasi'],
        'aile_sira_no' => $bulunan['AileSiraNumarasi'],
        'sira_no' => $bulunan['SiraNumarasi'],
        'durum' => $bulunan['OlumTarih'],
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'TC kimlik numarası bulunamadı',
        'aranan_tc' => $tc,
        'toplam_kayit' => count($tcpro_listesi),
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>