<?php
/**
 * Avukat Sicil Sorgulama API
 * Telegram: @unutur
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// JSON dosyasını oku
$json_file = __DIR__ . '/sicil_data.json';

if (!file_exists($json_file)) {
    echo json_encode([
        'success' => false,
        'error' => 'Veritabanı dosyası bulunamadı',
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$json_content = file_get_contents($json_file);
$veriler = json_decode($json_content, true);

// Parametreler
$avukat_sicil = $_GET['sicil'] ?? $_GET['avukat_sicil'] ?? null;
$avukat_tc = $_GET['tc'] ?? $_GET['avukat_tc'] ?? null;
$dosya_no = $_GET['dosya_no'] ?? null;
$kisi_ad = $_GET['kisi_ad'] ?? null;
$kisi_soyad = $_GET['kisi_soyad'] ?? null;

// Sicil no ile ara
if ($avukat_sicil) {
    $sonuc = [];
    foreach ($veriler as $kayit) {
        if (isset($kayit['AVUKAT_SICIL_NO']) && $kayit['AVUKAT_SICIL_NO'] == $avukat_sicil) {
            $sonuc[] = $kayit;
        }
    }
    if (count($sonuc) > 0) {
        echo json_encode([
            'success' => true,
            'aranan_sicil' => $avukat_sicil,
            'toplam' => count($sonuc),
            'kayitlar' => $sonuc,
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Sicil numarası kaydı bulunamadı',
            'aranan_sicil' => $avukat_sicil,
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
// TC ile ara
elseif ($avukat_tc) {
    $sonuc = [];
    foreach ($veriler as $kayit) {
        if (isset($kayit['AVUKAT_TC_KIMLIK_NO']) && $kayit['AVUKAT_TC_KIMLIK_NO'] == $avukat_tc) {
            $sonuc[] = $kayit;
        }
    }
    if (count($sonuc) > 0) {
        echo json_encode([
            'success' => true,
            'aranan_tc' => $avukat_tc,
            'toplam' => count($sonuc),
            'kayitlar' => $sonuc,
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'TC kimlik numarası kaydı bulunamadı',
            'aranan_tc' => $avukat_tc,
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
// Dosya no ile ara
elseif ($dosya_no) {
    $sonuc = [];
    foreach ($veriler as $kayit) {
        if (isset($kayit['DOSYA_NO']) && $kayit['DOSYA_NO'] == $dosya_no) {
            $sonuc[] = $kayit;
        }
    }
    if (count($sonuc) > 0) {
        echo json_encode([
            'success' => true,
            'aranan_dosya_no' => $dosya_no,
            'toplam' => count($sonuc),
            'kayitlar' => $sonuc,
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Dosya numarası kaydı bulunamadı',
            'aranan_dosya_no' => $dosya_no,
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
// Kişi ad soyad ile ara
elseif ($kisi_ad && $kisi_soyad) {
    $sonuc = [];
    foreach ($veriler as $kayit) {
        if (isset($kayit['KISI_ADI']) && isset($kayit['KISI_SOYAD']) && 
            strtolower($kayit['KISI_ADI']) == strtolower($kisi_ad) && 
            strtolower($kayit['KISI_SOYAD']) == strtolower($kisi_soyad)) {
            $sonuc[] = $kayit;
        }
    }
    if (count($sonuc) > 0) {
        echo json_encode([
            'success' => true,
            'aranan_kisi' => "$kisi_ad $kisi_soyad",
            'toplam' => count($sonuc),
            'kayitlar' => $sonuc,
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Kişi kaydı bulunamadı',
            'aranan_kisi' => "$kisi_ad $kisi_soyad",
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
// Parametre yok
else {
    echo json_encode([
        'success' => false,
        'error' => 'Sorgu parametresi gerekli',
        'kullanım' => [
            '/sicil.php?sicil=3860',
            '/sicil.php?tc=19402658634',
            '/sicil.php?dosya_no=2016/17736',
            '/sicil.php?kisi_ad=BERKAY&kisi_soyad=GENÇTÜRK'
        ],
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>