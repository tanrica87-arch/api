<?php
/**
 * Haber API - Türkiye ve Dünya Haberleri
 * Kaynak: RSS2JSON + BBC Türkçe
 * telegram : @unutur
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$action = isset($_GET['action']) ? trim($_GET['action']) : 'haberler';
$limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 50) : 10;
$kategori = isset($_GET['kategori']) ? trim($_GET['kategori']) : 'gundem';

// RSS Kaynakları
$rss_sources = [
    'gundem' => 'https://www.bbc.com/turkce/index.xml',
    'dunya' => 'https://www.bbc.com/turkce/dunya/index.xml',
    'ekonomi' => 'https://www.bbc.com/turkce/ekonomi/index.xml',
    'saglik' => 'https://www.bbc.com/turkce/saglik/index.xml',
    'teknoloji' => 'https://www.bbc.com/turkce/teknoloji/index.xml',
    'spor' => 'https://www.bbc.com/turkce/spor/index.xml',
    'kultur' => 'https://www.bbc.com/turkce/kultur/index.xml'
];

function fetchRSS($rss_url) {
    $api_url = "https://api.rss2json.com/v1/api.json?rss_url=" . urlencode($rss_url);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200 && $response) {
        return json_decode($response, true);
    }
    
    return null;
}

function temizle($text) {
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = strip_tags($text);
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

function kisaOzet($text, $length = 150) {
    $text = temizle($text);
    if (strlen($text) > $length) {
        $text = substr($text, 0, $length) . '...';
    }
    return $text;
}

// Ana haber akışı
if ($action == 'haberler') {
    $rss_url = isset($rss_sources[$kategori]) ? $rss_sources[$kategori] : $rss_sources['gundem'];
    $data = fetchRSS($rss_url);
    
    if (!$data || !isset($data['status']) || $data['status'] != 'ok') {
        echo json_encode([
            'success' => false,
            'error' => 'Haberler alınamadı',
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $haberler = [];
    $items = array_slice($data['items'], 0, $limit);
    
    foreach ($items as $item) {
        $haber = [
            'baslik' => temizle($item['title']),
            'ozet' => kisaOzet($item['description'] ?? $item['content'] ?? ''),
            'link' => $item['link'],
            'pub_date' => date('d.m.Y H:i', strtotime($item['pubDate'])),
            'resim' => $item['thumbnail'] ?? null,
            'kategori' => $kategori
        ];
        $haberler[] = $haber;
    }
    
    echo json_encode([
        'success' => true,
        'kategori' => $kategori,
        'toplam' => count($haberler),
        'kaynak' => $data['feed']['title'] ?? 'BBC Türkçe',
        'son_guncelleme' => date('d.m.Y H:i:s'),
        'haberler' => $haberler,
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Tek haber detayı
if ($action == 'detay') {
    $haber_link = isset($_GET['link']) ? urldecode($_GET['link']) : '';
    
    if (empty($haber_link)) {
        echo json_encode([
            'success' => false,
            'error' => 'Haber linki gerekli',
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Tüm kategorilerde ara
    $bulunan = null;
    foreach ($rss_sources as $cat => $url) {
        $data = fetchRSS($url);
        if ($data && isset($data['items'])) {
            foreach ($data['items'] as $item) {
                if ($item['link'] == $haber_link) {
                    $bulunan = [
                        'baslik' => temizle($item['title']),
                        'icerik' => temizle($item['content'] ?? $item['description'] ?? ''),
                        'ozet' => kisaOzet($item['description'] ?? $item['content'] ?? '', 300),
                        'link' => $item['link'],
                        'pub_date' => date('d.m.Y H:i', strtotime($item['pubDate'])),
                        'resim' => $item['thumbnail'] ?? null,
                        'kategori' => $cat
                    ];
                    break 2;
                }
            }
        }
    }
    
    if ($bulunan) {
        echo json_encode([
            'success' => true,
            'haber' => $bulunan,
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Haber bulunamadı',
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// Kategorileri listele
if ($action == 'kategoriler') {
    echo json_encode([
        'success' => true,
        'kategoriler' => array_keys($rss_sources),
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Varsayılan: yardım
echo json_encode([
    'success' => true,
    'api_adi' => 'Haber api',
    'versiyon' => '1.0',
    'endpoints' => [
        ['action' => 'haberler', 'params' => 'kategori=gundem&limit=10', 'aciklama' => 'Haberleri listeler'],
        ['action' => 'detay', 'params' => 'link=HABER_LINK', 'aciklama' => 'Haber detayını getirir'],
        ['action' => 'kategoriler', 'params' => '', 'aciklama' => 'Kategorileri listeler']
    ],
    'kategoriler' => array_keys($rss_sources),
    'telegram' => '@unutur'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>