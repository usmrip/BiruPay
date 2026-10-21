<?php
namespace App\Services;

use App\Models\Transaction;
use App\Models\Application;
use App\Models\GatewayConfig;
use App\Models\Log;
use App\Services\TelegramService;
use App\Services\EmailService;
use App\Services\WebhookDispatcherService;

class PaymentSyncService {
    /**
     * Check gateway and synchronize transaction status if still pending
     */
    public static function syncTransaction(array|int $trx): ?array {
        if (is_int($trx)) {
            $trx = Transaction::find($trx);
        }
        if (!$trx) {
            return null;
        }

        // Already completed
        if ($trx['status'] === 'success') {
            return $trx;
        }

        $gateway = $trx['gateway'] ?? '';
        $trxRef  = $trx['trx_reference'] ?? '';

        if (empty($trxRef)) {
            return $trx;
        }

        $isSettled = false;
        $paymentMethod = null;
        $gatewayRef = null;
        $rawPayload = null;

        // 1. MIDTRANS SYNC
        if ($gateway === 'midtrans') {
            $gwConfig = GatewayConfig::getByCode('midtrans');
            if ($gwConfig && !empty($gwConfig['server_key'])) {
                $serverKey = trim($gwConfig['server_key']);
                $isProd = !empty($gwConfig['is_production']);
                $url = ($isProd ? 'https://api.midtrans.com/v2/' : 'https://api.sandbox.midtrans.com/v2/') . urlencode($trxRef) . '/status';

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Accept: application/json',
                    'Authorization: Basic ' . base64_encode($serverKey . ':')
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                $resp = curl_exec($ch);
                curl_close($ch);

                $data = json_decode($resp, true);
                if ($data && isset($data['transaction_status'])) {
                    $rawPayload = $resp;
                    $trxStatus = $data['transaction_status'];
                    $fraudStatus = $data['fraud_status'] ?? 'accept';

                    if ($trxStatus === 'settlement' || ($trxStatus === 'capture' && $fraudStatus === 'accept')) {
                        $isSettled = true;
                        $paymentMethod = $data['payment_type'] ?? 'midtrans';
                        $gatewayRef = $data['transaction_id'] ?? null;
                    } elseif (in_array($trxStatus, ['deny', 'cancel'])) {
                        Transaction::update($trx['id'], [
                            'status' => 'failed',
                            'webhook_response_raw' => $rawPayload
                        ]);
                        return Transaction::find($trx['id']);
                    } elseif ($trxStatus === 'expire') {
                        Transaction::update($trx['id'], [
                            'status' => 'expired',
                            'webhook_response_raw' => $rawPayload
                        ]);
                        return Transaction::find($trx['id']);
                    }
                }
            }
        }
        // 2. DUITKU SYNC
        elseif ($gateway === 'duitku') {
            $gwConfig = GatewayConfig::getByCode('duitku');
            if ($gwConfig && !empty($gwConfig['server_key']) && !empty($gwConfig['merchant_id'])) {
                $merchantCode = trim($gwConfig['merchant_id']);
                $apiKey = trim($gwConfig['server_key']);
                $isProd = !empty($gwConfig['is_production']);
                $url = $isProd
                    ? 'https://api-prod.duitku.com/api/merchant/transactionStatus'
                    : 'https://api-sandbox.duitku.com/api/merchant/transactionStatus';

                $signature = md5($merchantCode . $trxRef . $apiKey);
                $payload = [
                    'merchantCode' => $merchantCode,
                    'merchantOrderId' => $trxRef,
                    'signature' => $signature
                ];

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Accept: application/json'
                ]);
                curl_setopt($ch, CURLOPT_TIMEOUT, 6);
                $resp = curl_exec($ch);
                curl_close($ch);

                $data = json_decode($resp, true);
                if ($data && isset($data['statusCode'])) {
                    $rawPayload = $resp;
                    if ($data['statusCode'] === '00') {
                        $isSettled = true;
                        $paymentMethod = $data['paymentMethod'] ?? 'duitku';
                        $gatewayRef = $data['reference'] ?? null;
                    } elseif ($data['statusCode'] === '02') {
                        Transaction::update($trx['id'], [
                            'status' => 'failed',
                            'webhook_response_raw' => $rawPayload
                        ]);
                        return Transaction::find($trx['id']);
                    }
                }
            }
        }

        // IF SETTLED: Update transaction & trigger notifications + webhook
        if ($isSettled) {
            $now = date('Y-m-d H:i:s');
            Transaction::update($trx['id'], [
                'status'               => 'success',
                'paid_at'              => $now,
                'payment_method'       => $paymentMethod ?: $trx['payment_method'],
                'gateway_reference'    => $gatewayRef ?: $trx['gateway_reference'],
                'webhook_response_raw' => $rawPayload ?: $trx['webhook_response_raw'],
            ]);

            $updatedTrx = Transaction::find($trx['id']);
            $app = Application::find($trx['app_id']);

            if ($app) {
                // Send Telegram Notification
                TelegramService::sendNotification($updatedTrx, $app);

                // Send Email Notification
                EmailService::sendNotification($updatedTrx, $app);

                // Dispatch Webhook to Client Application
                WebhookDispatcherService::dispatchToApp($updatedTrx['id']);
            }

            Log::info('payment_sync', "Transaction {$trxRef} auto-synced to success from {$gateway}");
            return $updatedTrx;
        }

        return $trx;
    }
}

