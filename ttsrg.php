<?php
/**
 * TikTok Kullanıcı Analiz API - Cloudflare Bypass
 * Telegram: @unutur
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$username = $_GET['username'] ?? null;

if (!$username) {
    echo json_encode([
        'success' => false,
        'error' => 'Kullanıcı adı gerekli',
        'kullanım' => '/tiktok.php?username=infazsiz6',
        'telegram' => '@unutur'
    ]);
    exit;
}

$username = ltrim($username, '@');

// 1. TikTok sayfasından secUid al
function getSecUid($username) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.tiktok.com/@{$username}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
        'Accept-Language: tr-TR,tr;q=0.9,en;q=0.8',
        'Cache-Control: no-cache',
        'Connection: keep-alive'
    ]);
    $html = curl_exec($ch);
    curl_close($ch);
    
    if (preg_match('/"secUid":"([^"]+)"/', $html, $match)) {
        return $match[1];
    }
    if (preg_match('/sec_user_id=([^&"\']+)/', $html, $match)) {
        return $match[1];
    }
    return null;
}

// 2. Countik API'ye istek at (daha güçlü header'larla)
function getCountikData($username, $secUid) {
    $url = "https://countik.com/api/analyze/?username={$username}&sec_user_id={$secUid}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json, text/plain, */*',
        'Accept-Language: tr-TR,tr;q=0.9,en;q=0.8',
        'Referer: https://countik.com/tr/tiktok-likes-generator',
        'Origin: https://countik.com',
        'Cache-Control: no-cache',
        'X-Requested-With: XMLHttpRequest'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'data' => json_decode($response, true)];
}

$secUid = getSecUid($username);

if (!$secUid) {
    echo json_encode([
        'success' => false,
        'error' => 'Kullanıcı bulunamadı',
        'username' => $username,
        'telegram' => '@unutur'
    ]);
    exit;
}

$result = getCountikData($username, $secUid);

if ($result['code'] !== 200) {
    echo json_encode([
        'success' => false,
        'error' => "API hatası: HTTP {$result['code']}",
        'username' => $username,
        'telegram' => '@unutur'
    ]);
    exit;
}

$data = $result['data'];

if (!$data || !isset($data['author'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Veri alınamadı',
        'username' => $username,
        'telegram' => '@unutur'
    ]);
    exit;
}

$author = $data['author'];

echo json_encode([
    'success' => true,
    'username' => $author['uniqueId'] ?? $username,
    'nickname' => $author['nickname'] ?? '',
    'followers' => $author['followerCount'] ?? 0,
    'following' => $author['followingCount'] ?? 0,
    'likes' => $author['heartCount'] ?? 0,
    'videos' => $author['videoCount'] ?? 0,
    'verified' => $author['verified'] ?? false,
    'bio' => $author['signature'] ?? '',
    'telegram' => '@unutur'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>