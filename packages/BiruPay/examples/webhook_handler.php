<?php
/**
 * Contoh Handler Webhook Notifikasi Pembayaran (Otomatis Lunas)
 */
require_once __DIR__ . '/../vendor/autoload.php';

use BiruPay\Client;
use BiruPay\Exception\BiruPayException;

$apiKey = 'YOUR_API_KEY';
$client = new Client($apiKey);

try {
    $webhook = $client->verifyWebhook();

    if ($webhook->isSuccess()) {
        $orderId = $webhook->getOrderId();
        $amount  = $webhook->getAmount();
        $metode  = $webhook->getPaymentMethod();
        $gateway = $webhook->getGateway();

        // 1. Eksekusi pembaruan status di database Anda:
        // markOrderAsPaid($orderId, $metode, $amount);

        // 2. Beri respon OK (200) ke Biru PayHub
        $webhook->respondOk();
    }
} catch (BiruPayException $e) {
    http_response_code(400);
    echo "Webhook error: " . $e->getMessage();
}
