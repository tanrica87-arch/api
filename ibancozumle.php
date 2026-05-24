<?php
/**
 * IBAN Çözümleme API
 * Telegram: @zahettim
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

class IBANParser
{
    private $iban;
    private $country_code;
    private $bank_code;
    private $branch_code;
    private $account_number;
    private $iban_length;
    
    // Ülke bazlı IBAN formatları
    private $country_formats = [
        'TR' => ['length' => 26, 'bank_pos' => 4, 'bank_len' => 5, 'branch_pos' => 9, 'branch_len' => 5, 'account_pos' => 14, 'account_len' => 12],
        'DE' => ['length' => 22, 'bank_pos' => 4, 'bank_len' => 8, 'account_pos' => 12, 'account_len' => 10],
        'GB' => ['length' => 22, 'bank_pos' => 4, 'bank_len' => 4, 'branch_pos' => 8, 'branch_len' => 6, 'account_pos' => 14, 'account_len' => 8],
        'FR' => ['length' => 27, 'bank_pos' => 4, 'bank_len' => 5, 'branch_pos' => 9, 'branch_len' => 5, 'account_pos' => 14, 'account_len' => 11],
        'US' => ['length' => 0, 'note' => 'ABD IBAN kullanmaz, Routing Number kullanır']
    ];
    
    // Türk bankaları kodları
    private $tr_banks = [
        '00010' => 'Türkiye Cumhuriyet Merkez Bankası',
        '00015' => 'Türkiye Vakıflar Bankası',
        '00032' => 'Türkiye Halk Bankası',
        '00034' => 'Türkiye İş Bankası',
        '00046' => 'Yapı ve Kredi Bankası',
        '00059' => 'Akbank',
        '00062' => 'Garanti BBVA',
        '00064' => 'QNB Finansbank',
        '00067' => 'Denizbank',
        '00069' => 'Ziraat Bankası',
        '00070' => 'Alternatif Bank',
        '00071' => 'Anadolubank',
        '00072' => 'Burgan Bank',
        '00073' => 'Şekerbank',
        '00074' => 'Fibabanka',
        '00075' => 'ING Bank',
        '00076' => 'HSBC',
        '00077' => 'Citibank',
        '00078' => 'Deutsche Bank',
        '00082' => 'Odeabank',
        '00089' => 'Vakıf Katılım',
        '00090' => 'Ziraat Katılım',
        '00091' => 'Kuveyt Türk',
        '00092' => 'Albaraka Türk',
        '00123' => 'ICBC Turkey',
        '00142' => 'Aktif Bank'
    ];
    
    public function __construct($iban)
    {
        // Boşlukları temizle ve büyük harfe çevir
        $this->iban = strtoupper(preg_replace('/\s+/', '', $iban));
        $this->parse();
    }
    
    private function parse()
    {
        // IBAN uzunluğu kontrolü
        $this->iban_length = strlen($this->iban);
        
        // Ülke kodunu al (ilk 2 karakter)
        $this->country_code = substr($this->iban, 0, 2);
        
        if (!isset($this->country_formats[$this->country_code])) {
            $this->error = 'Desteklenmeyen ülke kodu: ' . $this->country_code;
            return;
        }
        
        $format = $this->country_formats[$this->country_code];
        
        if ($format['length'] > 0 && $this->iban_length != $format['length']) {
            $this->error = 'IBAN uzunluğu hatalı! Beklenen: ' . $format['length'] . ', Girilen: ' . $this->iban_length;
            return;
        }
        
        // Banka kodu (ülkeye göre farklı konumlarda)
        if (isset($format['bank_pos'])) {
            $this->bank_code = substr($this->iban, $format['bank_pos'], $format['bank_len']);
        }
        
        // Şube kodu (varsa)
        if (isset($format['branch_pos'])) {
            $this->branch_code = substr($this->iban, $format['branch_pos'], $format['branch_len']);
        }
        
        // Hesap numarası (ülkeye göre)
        if (isset($format['account_pos'])) {
            $account_raw = substr($this->iban, $format['account_pos'], $format['account_len']);
            $this->account_number = ltrim($account_raw, '0');
        }
        
        $this->success = true;
    }
    
    public function getResult()
    {
        if (isset($this->error)) {
            return [
                'success' => false,
                'error' => $this->error,
                'iban' => $this->iban
            ];
        }
        
        $result = [
            'success' => true,
            'iban' => $this->iban,
            'country_code' => $this->country_code,
            'bank_name' => $this->getBankName(),
            'bank_code' => $this->bank_code ?? null,
            'branch_code' => $this->branch_code ?? null,
            'account_number' => $this->account_number ?? null,
            'telegram' => '@zahettim'
        ];
        
        return $result;
    }
    
    private function getBankName()
    {
        if ($this->country_code == 'TR' && isset($this->tr_banks[$this->bank_code])) {
            return $this->tr_banks[$this->bank_code];
        }
        return 'Banka bilgisi bulunamadı';
    }
}

// API isteğini işle
if ($_SERVER['REQUEST_METHOD'] === 'GET' || $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $iban = null;
    
    // GET parametresi
    if (isset($_GET['iban'])) {
        $iban = $_GET['iban'];
    }
    // POST verisi
    elseif (isset($_POST['iban'])) {
        $iban = $_POST['iban'];
    }
    // JSON body
    else {
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['iban'])) {
            $iban = $input['iban'];
        }
    }
    
    if (!$iban) {
        echo json_encode([
            'success' => false,
            'error' => 'IBAN parametresi gerekli',
            'usage' => [
                'GET' => '/cozumle_iban?iban=TR330006100519786457841326',
                'POST' => '{"iban": "TR330006100519786457841326"}',
                'telegram' => '@zahettim'
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    $parser = new IBANParser($iban);
    echo json_encode($parser->getResult(), JSON_UNESCAPED_UNICODE);
}
?>
