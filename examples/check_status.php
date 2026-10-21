<?php
/**
 * Contoh Pengecekan Status Transaksi Secara Real-Time
 */
require_once __DIR__ . '/../vendor/autoload.php';

use BiruPay\Client;
use BiruPay\Exception\BiruPayException;

$apiKey = 'YOUR_API_KEY';
$client = new Client($apiKey);

try {
    $orderId = 'INV-12345';
    $status = $client->checkStatus($orderId);

    echo "Status Pesanan " . $status['order_id'] . ": " . strtoupper($status['status']) . "\n";
    echo "Metode: " . ($status['payment_method'] ?? '-') . "\n";
    echo "Jumlah: Rp " . number_format($status['amount'], 0, ',', '.') . "\n";

} catch (BiruPayException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
