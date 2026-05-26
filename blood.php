<?php
/**
 * Kanlı Yazı Logo API - Photofunia (Kesin Çözüm)
 * Telegram: @unutur
 */

$text = isset($_GET['text']) ? $_GET['text'] : null;

if (!$text) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'Yazı gerekli']);
    exit;
}

// 1. İsteği Photofunia'ya gönder
$url = "https://m.photofunia.com/categories/halloween/blood_writing";
$boundary = "----WebKitFormBoundary" . md5(rand());
$body = "--{$boundary}\r\n";
$body .= "Content-Disposition: form-data; name=\"text\"\r\n\r\n{$text}\r\n";
$body .= "--{$boundary}--\r\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: multipart/form-data; boundary={$boundary}",
    "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
]);
$response = curl_exec($ch);
curl_close($ch);

// 2. Sonuç sayfasından büyük görselin linkini bul
preg_match('/<a href="(.*?)\?download".*?Large<\/a>/s', $response, $matches);

if (isset($matches[1])) {
    $image_url = $matches[1];
    
    // 3. Görseli indir ve göster
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

// 4. Hata durumu
header('Content-Type: application/json');
echo json_encode(['success' => false, 'error' => 'Görsel oluşturulamadı', 'text' => $text]);
?>