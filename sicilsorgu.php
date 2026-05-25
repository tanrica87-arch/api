<?php
/**
 * Sicil Sorgulama API - JSON Array Formatı
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

$content = file_get_contents($json_file);
$tum_veriler = json_decode($content, true);

if (!is_array($tum_veriler)) {
    echo json_encode([
        'success' => false,
        'error' => 'JSON formatı hatalı',
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Parametreler
$sicil = $_GET['sicil'] ?? $_GET['avukat_sicil'] ?? null;
$tc = $_GET['tc'] ?? $_GET['avukat_tc'] ?? null;
$dosya_no = $_GET['dosya_no'] ?? null;
$kisi_ad = $_GET['kisi_ad'] ?? null;
$kisi_soyad = $_GET['kisi_soyad'] ?? null;

// Sicil no ile ara
if ($sicil) {
    $sonuc = [];
    foreach ($tum_veriler as $kayit) {
        if (isset($kayit['AVUKAT_SICIL_NO']) && (string)$kayit['AVUKAT_SICIL_NO'] === (string)$sicil) {
            $sonuc[] = $kayit;
        }
    }
    if (count($sonuc) > 0) {
        echo json_encode([
            'success' => true,
            'sorgu_tipi' => 'sicil_no',
            'aranan' => $sicil,
            'toplam' => count($sonuc),
            'kayitlar' => $sonuc,
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Sicil numarası kaydı bulunamadı',
            'aranan_sicil' => $sicil,
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
// TC ile ara
elseif ($tc) {
    $sonuc = [];
    foreach ($tum_veriler as $kayit) {
        if (isset($kayit['AVUKAT_TC_KIMLIK_NO']) && (string)$kayit['AVUKAT_TC_KIMLIK_NO'] === (string)$tc) {
            $sonuc[] = $kayit;
        }
    }
    if (count($sonuc) > 0) {
        echo json_encode([
            'success' => true,
            'sorgu_tipi' => 'tc_kimlik',
            'aranan' => $tc,
            'toplam' => count($sonuc),
            'kayitlar' => $sonuc,
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'TC kimlik numarası kaydı bulunamadı',
            'aranan_tc' => $tc,
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
// Dosya no ile ara
elseif ($dosya_no) {
    $sonuc = [];
    foreach ($tum_veriler as $kayit) {
        if (isset($kayit['DOSYA_NO']) && $kayit['DOSYA_NO'] == $dosya_no) {
            $sonuc[] = $kayit;
        }
    }
    if (count($sonuc) > 0) {
        echo json_encode([
            'success' => true,
            'sorgu_tipi' => 'dosya_no',
            'aranan' => $dosya_no,
            'toplam' => count($sonuc),
            'kayitlar' => $sonuc,
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Dosya numarası kaydı bulunamadı',
            'aranan_dosya' => $dosya_no,
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
// Kişi ad soyad ile ara
elseif ($kisi_ad && $kisi_soyad) {
    $sonuc = [];
    foreach ($tum_veriler as $kayit) {
        if (isset($kayit['KISI_ADI']) && isset($kayit['KISI_SOYAD']) && 
            strtolower($kayit['KISI_ADI']) == strtolower($kisi_ad) && 
            strtolower($kayit['KISI_SOYAD']) == strtolower($kisi_soyad)) {
            $sonuc[] = $kayit;
        }
    }
    if (count($sonuc) > 0) {
        echo json_encode([
            'success' => true,
            'sorgu_tipi' => 'kisi_adi_soyadi',
            'aranan' => "$kisi_ad $kisi_soyad",
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
            'sicil' => '/sicilsorgu.php?sicil=2925',
            'tc' => '/sicilsorgu.php?tc=27175405034',
            'dosya_no' => '/sicilsorgu.php?dosya_no=2016/17736',
            'kisi_ad_soyad' => '/sicilsorgu.php?kisi_ad=FURKAN&kisi_soyad=BAŞ'
        ],
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>