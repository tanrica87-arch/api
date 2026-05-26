<?php
/**
 * Kanlı Yazı Logo Oluşturma API
 * Telegram: @unutur
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$text = isset($_GET['text']) ? $_GET['text'] : (isset($_POST['text']) ? $_POST['text'] : null);

if (!$text) {
    echo json_encode([
        'success' => false,
        'error' => 'Yazı gerekli',
        'kullanım' => '/blood.php?text=Merhaba',
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Photofunia API
$url = "https://m.photofunia.com/categories/halloween/blood_writing";

$boundary = "----WebKitFormBoundary" . substr(md5(rand()), 0, 20);
$body = "--{$boundary}\r\n";
$body .= "Content-Disposition: form-data; name=\"text\"\r\n\r\n";
$body .= "{$text}\r\n";
$body .= "--{$boundary}--\r\n";

$headers = [
    "Content-Type: multipart/form-data; boundary={$boundary}",
    "Referer: https://m.photofunia.com/categories/halloween/blood_writing",
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    echo json_encode([
        'success' => false,
        'error' => "API hatası: HTTP {$httpCode}",
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Görsel linkini bul
preg_match('/<ul class="images">.*?<a href="(.*?)\?download".*?Large<\/a>.*?<\/li>/s', $response, $matches);

if (isset($matches[1])) {
    $image_url = $matches[1];
    
    // Resmi indir ve base64'e çevir veya direkt URL döndür
    echo json_encode([
        'success' => true,
        'text' => $text,
        'image_url' => $image_url,
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Görsel linki bulunamadı',
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>