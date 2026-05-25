<?php
/**
 * Deftere Yazı Yazdır API
 * Telegram: @unutur
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER["REQUEST_METHOD"] == "GET" || $_SERVER["REQUEST_METHOD"] == "POST") {
    
    $metin = null;
    
    if (isset($_GET['metin'])) {
        $metin = $_GET['metin'];
    } elseif (isset($_POST['metin'])) {
        $metin = $_POST['metin'];
    } else {
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['metin'])) {
            $metin = $input['metin'];
        }
    }
    
    if (!$metin) {
        echo json_encode([
            'success' => false,
            'error' => 'Metin parametresi gerekli',
            'usage' => [
                'GET' => '/?metin=Merhaba',
                'POST' => '{"metin": "Merhaba"}',
                'telegram' => '@unutur'
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // API'ye istek at
    $api_url = "http://apis.xditya.me/write?text=" . urlencode($metin);
    
    // Fotoğrafı indir
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $image_data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200 && $image_data) {
        // Benzersiz dosya adı oluştur
        $filename = md5($metin . time()) . '.png';
        $filepath = __DIR__ . '/uploads/' . $filename;
        
        // Klasör yoksa oluştur
        if (!is_dir(__DIR__ . '/uploads')) {
            mkdir(__DIR__ . '/uploads', 0777, true);
        }
        
        // Fotoğrafı kaydet
        file_put_contents($filepath, $image_data);
        
        // URL oluştur
        $protocol = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $image_url = $protocol . $host . '/uploads/' . $filename;
        
        echo json_encode([
            'success' => true,
            'image' => $image_url,
            'metin' => $metin,
            'telegram' => '@unutur'
        ], JSON_UNESCAPED_UNICODE);
        
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Fotoğraf oluşturulamadı',
            'telegram' => '@unutur'
        ], JSON_UNESCAPED_UNICODE);
    }
    
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Sadece GET ve POST istekleri kabul edilir',
        'telegram' => '@unutur'
    ], JSON_UNESCAPED_UNICODE);
}
?>