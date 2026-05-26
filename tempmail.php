<?php
/**
 * Temp Mail API - Geçici Email ve Gelen Kutusu
 * Telegram: @unutur
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : 'create');

// Temp-Mail API
$api_url = "https://api.internal.temp-mail.io/api/v3";

if ($action == 'create') {
    // Yeni email oluştur
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url . "/email/new");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['expires_in_seconds' => 300]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: Mozilla/5.0',
        'Content-Type: application/json',
        'Origin: https://temp-mail.io',
        'Referer: https://temp-mail.io/'
    ]);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $data = json_decode($response, true);
        echo json_encode([
            'success' => true,
            'action' => 'created',
            'email' => $data['email'],
            'email_id' => $data['id'],
            'expires_in' => $data['expires_in_seconds'] . ' saniye',
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Email oluşturulamadı',
            'telegram' => '@unutur'
        ]);
    }
} 
elseif ($action == 'inbox') {
    // Gelen kutusunu kontrol et
    $email_id = isset($_GET['email_id']) ? $_GET['email_id'] : (isset($_POST['email_id']) ? $_POST['email_id'] : null);
    
    if (!$email_id) {
        echo json_encode([
            'success' => false,
            'error' => 'Email ID gerekli',
            'kullanım' => '/tempmail.php?action=inbox&email_id=ID',
            'telegram' => '@unutur'
        ]);
        exit;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url . "/email/{$email_id}/messages");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $messages = json_decode($response, true);
        
        if (count($messages) > 0) {
            $formatted_messages = [];
            foreach ($messages as $msg) {
                $formatted_messages[] = [
                    'id' => $msg['id'],
                    'subject' => $msg['mail_subject'],
                    'from' => $msg['mail_from'],
                    'body' => $msg['mail_text_only'] ?? substr($msg['mail_html'], 0, 500),
                    'received_at' => date('Y-m-d H:i:s', $msg['mail_timestamp'])
                ];
            }
            
            echo json_encode([
                'success' => true,
                'action' => 'inbox',
                'total' => count($messages),
                'messages' => $formatted_messages,
                'telegram' => '@unutur'
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode([
                'success' => true,
                'action' => 'inbox',
                'total' => 0,
                'message' => 'Henüz mesaj yok',
                'telegram' => '@unutur'
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Gelen kutusu alınamadı',
            'telegram' => '@unutur'
        ]);
    }
}
elseif ($action == 'read') {
    // Tek mesaj oku
    $message_id = isset($_GET['message_id']) ? $_GET['message_id'] : (isset($_POST['message_id']) ? $_POST['message_id'] : null);
    
    if (!$message_id) {
        echo json_encode([
            'success' => false,
            'error' => 'Message ID gerekli',
            'telegram' => '@unutur'
        ]);
        exit;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url . "/email/messages/{$message_id}");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    
    if ($response) {
        $msg = json_decode($response, true);
        echo json_encode([
            'success' => true,
            'action' => 'message',
            'id' => $msg['id'],
            'subject' => $msg['mail_subject'],
            'from' => $msg['mail_from'],
            'body_text' => $msg['mail_text_only'] ?? '',
            'body_html' => $msg['mail_html'] ?? '',
            'received_at' => date('Y-m-d H:i:s', $msg['mail_timestamp']),
            'telegram' => '@unutur'
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Mesaj bulunamadı',
            'telegram' => '@unutur'
        ]);
    }
}
else {
    echo json_encode([
        'success' => false,
        'error' => 'Geçersiz aksiyon',
        'aksiyonlar' => ['create', 'inbox', 'read'],
        'kullanım' => '/tempmail.php?action=create',
        'telegram' => '@unutur'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>