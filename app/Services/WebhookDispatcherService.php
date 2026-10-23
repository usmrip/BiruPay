<?php
namespace App\Services;

use App\Models\Transaction;
use App\Models\Application;
use App\Models\Log;

class WebhookDispatcherService {
    /**
     * Dispatch payment notification to destination Biru application
     */
    public static function dispatchToApp(int $transactionId): bool {
        $trx = Transaction::getWithApplication($transactionId);
        if (!$trx) {
            Log::error('webhook_dispatch', 'Transaction not found for webhook forwarding: ' . $transactionId);
            return false;
        }

        $webhookUrl = trim($trx['webhook_url'] ?? '');
        if (empty($webhookUrl)) {
            Log::info('webhook_dispatch', 'No webhook URL configured for application ' . ($trx['app_code'] ?? 'Unknown'));
            Transaction::update($trx['id'], [
                'is_app_notified' => 1,
                'app_notify_status' => 'SKIPPED_NO_URL',
                'app_notify_attempts' => ($trx['app_notify_attempts'] ?? 0) + 1
            ]);
            return true;
        }

        $app = Application::find($trx['app_id']);
        $apiKey = $app['api_key'] ?? '';

        $payload = [
            'event' => 'payment.' . $trx['status'],
            'timestamp' => time(),
            'data' => [
                'order_id'         => $trx['order_id'],
                'trx_reference'    => $trx['trx_reference'],
                'gateway'          => $trx['gateway'],
                'gateway_reference'=> $trx['gateway_reference'],
                'payment_method'   => $trx['payment_method'],
                'amount'           => (float) $trx['amount'],
                'fee'              => (float) $trx['fee'],
                'status'           => $trx['status'],
                'customer_name'    => $trx['customer_name'],
                'customer_email'   => $trx['customer_email'],
                'customer_phone'   => $trx['customer_phone'],
                'item_details'     => json_decode($trx['item_details'] ?? '[]', true),
                'paid_at'          => $trx['paid_at'],
                'created_at'       => $trx['created_at'],
            ]
        ];

        $jsonPayload = json_encode($payload);
        $signature = hash_hmac('sha256', $jsonPayload, $apiKey);

        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'X-Biru-Signature: ' . $signature,
            'X-Biru-Event: payment.' . $trx['status'],
            'User-Agent: Biru-PayHub-Notifier/1.0'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POSTREDIR, 3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $attempts = ($trx['app_notify_attempts'] ?? 0) + 1;
        $statusStr = $curlError ? 'CURL_ERR: ' . substr($curlError, 0, 40) : "HTTP_{$httpCode}";
        $isSuccess = ($httpCode >= 200 && $httpCode < 300);

        Transaction::update($trx['id'], [
            'is_app_notified'     => $isSuccess ? 1 : 0,
            'app_notify_status'   => $statusStr,
            'app_notify_attempts' => $attempts
        ]);

        if (!$isSuccess) {
            Log::error('webhook_dispatch', "Webhook forwarding failed to {$webhookUrl}", [
                'http_code' => $httpCode,
                'error'     => $curlError,
                'response'  => substr($response ?: '', 0, 200)
            ]);
            return false;
        }

        Log::info('webhook_dispatch', "Webhook forwarded successfully to {$webhookUrl} (HTTP {$httpCode})");
        return true;
    }
}

