<?php
/**
 * Kanlı Yazı Logo API - Direkt Görsel Göster
 * Telegram: @zahettim
 */

$text = isset($_GET['text']) ? $_GET['text'] : (isset($_POST['text']) ? $_POST['text'] : null);

if (!$text) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Yazı gerekli',
        'kullanım' => '/blood.php?text=Merhaba',
        'telegram' => '@zahettim'
    ]);
    exit;
}

// Photofunia'ya istek at
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
curl_close($ch);

// Görsel linkini bul
preg_match('/<ul class="images">.*?<a href="(.*?)\?download".*?Large<\/a>.*?<\/li>/s', $response, $matches);

if (isset($matches[1])) {
    $image_url = $matches[1];
    
    // Resmi indir ve direkt göster
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $image_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    $image_data = curl_exec($ch);
    $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);
    
    if ($image_data) {
        header('Content-Type: ' . $content_type);
        header('Content-Length: ' . strlen($image_data));
        echo $image_data;
        exit;
    }
}

// Hata durumunda JSON dön
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'error' => 'Görsel oluşturulamadı',
    'text' => $text,
    'telegram' => '@zahettim'
]);
?>