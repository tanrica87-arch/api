<?php
/**
 * TikTok Kullanıcı Analiz API
 * Telegram: @zahettim
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$username = $_GET['username'] ?? $_POST['username'] ?? null;

if (!$username) {
    echo json_encode([
        'success' => false,
        'error' => 'Kullanıcı adı gerekli',
        'kullanım' => '/tiktok.php?username=infazsiz6',
        'telegram' => '@zahettim'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$username = ltrim($username, '@');

// TikTok sayfasından secUid al
function getSecUid($username) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.tiktok.com/@{$username}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
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

$secUid = getSecUid($username);

if (!$secUid) {
    echo json_encode([
        'success' => false,
        'error' => 'Kullanıcı bulunamadı veya secUid alınamadı',
        'username' => $username,
        'telegram' => '@zahettim'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Countik API
$url = "https://countik.com/api/analyze/";
$params = [
    'username' => $username,
    'sec_user_id' => $secUid
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url . '?' . http_build_query($params));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
curl_setopt($ch, CURLOPT_TIMEOUT, 15);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode != 200) {
    echo json_encode([
        'success' => false,
        'error' => "API hatası: HTTP {$httpCode}",
        'username' => $username,
        'telegram' => '@zahettim'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode($response, true);

if (!$data || !isset($data['author'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Veri alınamadı',
        'username' => $username,
        'telegram' => '@zahettim'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$author = $data['author'];

$result = [
    'success' => true,
    'username' => $author['uniqueId'] ?? $username,
    'nickname' => $author['nickname'] ?? '',
    'followers' => $author['followerCount'] ?? 0,
    'following' => $author['followingCount'] ?? 0,
    'likes' => $author['heartCount'] ?? 0,
    'videos' => $author['videoCount'] ?? 0,
    'verified' => $author['verified'] ?? false,
    'bio' => $author['signature'] ?? '',
    'avatar' => $author['avatarThumb'] ?? '',
    'telegram' => '@zahettim'
];

// Engagement rates
if (isset($data['engagement_rates'])) {
    $result['engagement_rate'] = $data['engagement_rates']['total_rate'] ?? 0;
}

// Son video
if (isset($data['videos'][0])) {
    $video = $data['videos'][0];
    $result['last_video'] = [
        'likes' => $video['likes'] ?? 0,
        'plays' => $video['plays'] ?? 0,
        'comments' => $video['comments'] ?? 0,
        'shares' => $video['shares'] ?? 0,
        'desc' => $video['desc'] ?? ''
    ];
}

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>