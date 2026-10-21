<?php
namespace App\Gateways;

use App\Core\Request;
use App\Models\GatewayConfig;
use App\Models\Log;
use App\Core\View;

class DuitkuGateway implements PaymentGatewayInterface {
    private ?array $config;

    public function __construct(?array $config = null) {
        $this->config = $config ?: GatewayConfig::getByCode('duitku');
    }

    public function isConfigured(): bool {
        return !empty($this->config['merchant_id']) && !empty($this->config['server_key']);
    }

    private function isProduction(): bool {
        return !empty($this->config['is_production']);
    }

    private function getApiUrl(): string {
        return $this->isProduction()
            ? 'https://api-prod.duitku.com/api/merchant/createInvoice'
            : 'https://api-sandbox.duitku.com/api/merchant/createInvoice';
    }

    public function getFrontendConfig(): array {
        return [
            'gateway' => 'duitku',
            'script_url' => $this->isProduction()
                ? 'https://app-prod.duitku.com/lib/js/duitku.js'
                : 'https://app-sandbox.duitku.com/lib/js/duitku.js',
            'is_production' => $this->isProduction(),
        ];
    }

    public function createPayment(array $params): array {
        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'Konfigurasi Duitku belum lengkap (Merchant Code atau API Key belum diisi).'
            ];
        }

        $merchantCode = trim($this->config['merchant_id']);
        $apiKey       = trim($this->config['server_key']);
        $amount       = (int) round($params['amount']);
        $orderId      = $params['trx_reference'];
        $expiryPeriod = (int) ($this->config['additional']['expiry_period'] ?? 1440);

        // Duitku POP requires headers: x-duitku-timestamp, x-duitku-signature, x-duitku-merchantcode
        $timestamp = round(microtime(true) * 1000);
        $headerSignature = hash_hmac('sha256', $merchantCode . $timestamp, $apiKey);

        // Body signature (md5 of merchantCode + orderId + amount + apiKey)
        $bodySignature = md5($merchantCode . $orderId . $amount . $apiKey);

        $callbackUrl = View::url('webhook/duitku');
        $returnUrl   = View::url('payment/return?ref=' . urlencode($orderId));

        $payload = [
            'paymentAmount'    => $amount,
            'merchantOrderId'  => $orderId,
            'productDetails'   => substr($params['item_name'] ?? 'Pembayaran Layanan Biru', 0, 100),
            'email'            => $params['customer_email'] ?? 'customer@biru.co.id',
            'phoneNumber'      => $params['customer_phone'] ?? '08123456789',
            'additionalParam'  => json_encode([
                'app_code' => $params['app_code'] ?? 'BIRU',
                'app_id'   => $params['app_id'] ?? '',
                'order_id' => $params['order_id'] ?? ''
            ]),
            'merchantUserInfo' => $params['customer_name'] ?? 'Pelanggan Biru',
            'customerVaName'   => $params['customer_name'] ?? 'Pelanggan Biru',
            'callbackUrl'      => $callbackUrl,
            'returnUrl'        => $returnUrl,
            'signature'        => $bodySignature,
            'expiryPeriod'     => $expiryPeriod
        ];

        $ch = curl_init($this->getApiUrl());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) Biru-PayHub/1.0',
            'x-duitku-timestamp: ' . $timestamp,
            'x-duitku-signature: ' . $headerSignature,
            'x-duitku-merchantcode: ' . $merchantCode
        ]);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Biru-PayHub/1.0');
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
        curl_setopt($ch, CURLOPT_TIMEOUT, 8);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            Log::error('duitku', 'Duitku cURL error', ['error' => $curlError, 'payload' => $payload]);
            return [
                'success' => false,
                'message' => 'Gagal menghubungi server Duitku: ' . $curlError
            ];
        }

        $result = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300 && !empty($result['reference'])) {
            return [
                'success' => true,
                'gateway' => 'duitku',
                'reference' => $result['reference'],
                'trx_reference' => $orderId,
                'payment_url' => $result['paymentUrl'] ?? '',
                'status_code' => $result['statusCode'] ?? '00',
                'mode' => $this->isProduction() ? 'production' : 'sandbox',
                'script_url' => $this->getFrontendConfig()['script_url']
            ];
        }

        $errorMessage = $result['statusMessage'] ?? $result['message'] ?? 'Gagal membuat invoice Duitku (HTTP ' . $httpCode . ')';
        Log::error('duitku', 'Duitku createInvoice failed', ['http_code' => $httpCode, 'response' => $result, 'payload' => $payload]);

        return [
            'success' => false,
            'message' => 'Duitku Error: ' . $errorMessage
        ];
    }

    public function verifyWebhook(Request $request): array {
        // Duitku may send either POST Form URL-encoded or JSON
        $params = $request->all();
        $raw = $request->rawBody();

        $merchantCode = $params['merchantCode'] ?? '';
        $amount       = $params['amount'] ?? '';
        $orderId      = $params['merchantOrderId'] ?? '';
        $signature    = $params['signature'] ?? '';
        $resultCode   = $params['resultCode'] ?? '';
        $reference    = $params['reference'] ?? '';

        if (empty($orderId) || empty($signature)) {
            return [
                'is_valid' => false,
                'message' => 'Parameter callback Duitku tidak lengkap.'
            ];
        }

        $apiKey = trim($this->config['server_key'] ?? '');
        $expectedSignature = md5($merchantCode . $amount . $orderId . $apiKey);

        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('duitku', 'Duitku invalid signature', [
                'expected' => $expectedSignature,
                'received' => $signature,
                'order_id' => $orderId
            ]);
            return [
                'is_valid' => false,
                'message' => 'Signature callback Duitku tidak cocok.'
            ];
        }

        $status = ($resultCode === '00') ? 'success' : 'failed';

        return [
            'is_valid' => true,
            'trx_reference' => $orderId,
            'gateway_reference' => $reference,
            'status' => $status,
            'payment_method' => $params['paymentCode'] ?? 'duitku_payment',
            'amount' => (float) $amount,
            'raw_payload' => $raw,
            'original_status' => $resultCode,
            'message' => 'Verifikasi webhook Duitku sukses.'
        ];
    }
}

