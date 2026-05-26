<?php
/**
 * Instagram Kullanıcı Analiz API
 * Telegram: @unutur
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$username = isset($_GET['username']) ? $_GET['username'] : null;

if (!$username) {
    echo json_encode([
        'success' => false,
        'error' => 'Kullanıcı adı gerekli',
        'kullanım' => '/instagram.php?username=infazsiz6',
        'telegram' => '@unutur'
    ]);
    exit;
}

$username = ltrim($username, '@');

// Instagram Graph API (public bilgiler için)
$url = "https://www.instagram.com/{$username}/?__a=1&__d=1";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Accept: application/json, text/plain, */*',
    'Accept-Language: tr-TR,tr;q=0.9,en;q=0.8'
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode == 200) {
    $data = json_decode($response, true);
    
    // Instagram API yanıt yapısı
    if (isset($data['graphql']['user'])) {
        $user = $data['graphql']['user'];
        
        echo json_encode([
            'success' => true,
            'username' => $user['username'],
            'full_name' => $user['full_name'],
            'followers' => $user['edge_followed_by']['count'],
            'following' => $user['edge_follow']['count'],
            'posts' => $user['edge_owner_to_timeline_media']['count'],
            'verified' => $user['is_verified'],
            'private' => $user['is_private'],
            'bio' => $user['biography'],
            'avatar' => $user['profile_pic_url_hd'],
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Kullanıcı bulunamadı',
            'username' => $username,
            'telegram' => '@unutur'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => "API hatası: HTTP {$httpCode}",
        'username' => $username,
        'telegram' => '@unutur'
    ]);
}
?>