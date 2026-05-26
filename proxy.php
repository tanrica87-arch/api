<?php
/**
 * Free Proxy List API - Dosya Olarak İndirme
 * Telegram: @unutur
 */

header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="proxies.txt"');
header('Access-Control-Allow-Origin: *');

$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
if ($limit > 500) $limit = 500;

// Free proxy kaynakları
$sources = [
    'https://api.proxyscrape.com/v2/?request=getproxies&protocol=http&timeout=10000&country=all',
    'https://raw.githubusercontent.com/TheSpeedX/PROXY-List/master/http.txt',
    'https://raw.githubusercontent.com/ShiftyTR/Proxy-List/master/http.txt',
    'https://raw.githubusercontent.com/monosans/proxy-list/main/proxies/http.txt',
    'https://www.proxy-list.download/api/v1/get?type=http'
];

$all_proxies = [];

foreach ($sources as $source) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $source);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $lines = explode("\n", $response);
        foreach ($lines as $line) {
            $line = trim($line);
            if (preg_match('/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d{2,5}$/', $line)) {
                $all_proxies[] = $line;
            }
        }
    }
}

$all_proxies = array_unique($all_proxies);
$proxies = array_slice($all_proxies, 0, $limit);

// Dosya olarak döndür
echo "# Proxy Listesi\n";
echo "# Toplam: " . count($all_proxies) . " | Gosterilen: " . count($proxies) . "\n";
echo "# Olusturma: " . date('Y-m-d H:i:s') . "\n";
echo "# Telegram: @unutur\n\n";

foreach ($proxies as $proxy) {
    echo $proxy . "\n";
}
?>