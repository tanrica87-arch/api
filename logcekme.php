<?php
/**
 * Domain Credential Checker API
 * Çoklu log dosyasını tarar (urlog.txt, urlog2.txt, urlog3.txt)
 * telegram : @unutur
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$domain = isset($_GET['domain']) ? trim($_GET['domain']) : '';

if (empty($domain)) {
    echo json_encode([
        'success' => false,
        'error' => '❌ Domain gerekli',
        'ornek' => '/?domain=terrapizza.com.tr',
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Taranacak dosyalar
$log_files = ['urlog.txt', 'urlog2.txt', 'urlog3.txt'];
$all_results = [];

foreach ($log_files as $log_file) {
    if (!file_exists($log_file)) {
        continue; // Dosya yoksa atla
    }
    
    $content = file_get_contents($log_file);
    $lines = explode("\n", $content);
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        // Domain kontrolü
        if (stripos($line, $domain) !== false) {
            $all_results[] = [
                'file' => $log_file,
                'line' => $line
            ];
        }
    }
}

if (empty($all_results)) {
    echo json_encode([
        'success' => true,
        'domain' => $domain,
        'total' => 0,
        'message' => 'Bu domain için kayıtlı bilgi bulunamadı',
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Sonuçları düzenle (dosya bazlı grupla)
$grouped = [];
foreach ($all_results as $item) {
    $file = $item['file'];
    if (!isset($grouped[$file])) {
        $grouped[$file] = [];
    }
    $grouped[$file][] = $item['line'];
}

echo json_encode([
    'success' => true,
    'domain' => $domain,
    'total' => count($all_results),
    'files_scanned' => array_values(array_filter($log_files, 'file_exists')),
    'results' => $grouped,
    'telegram' => '@unutur'
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>