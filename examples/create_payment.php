<?php
/**
 * Contoh Pembuatan Tagihan Pembayaran dengan BiruPay SDK
 */
require_once __DIR__ . '/../vendor/autoload.php';

use BiruPay\Client;
use BiruPay\Exception\BiruPayException;

$apiKey = 'YOUR_API_KEY';
$client = new Client($apiKey);

try {
    $payment = $client->createPayment([
        'order_id'       => 'INV-' . time(),
        'amount'         => 50000,
        'customer_name'  => 'Budi Santoso',
        'customer_email' => 'budi@example.com',
        'customer_phone' => '081234567890',
        'item_name'      => 'Aktivasi Layanan Biru VIP'
    ]);

    echo "Tagihan berhasil dibuat!\n";
    echo "Referensi Hub: " . $payment['trx_reference'] . "\n";
    echo "Gateway Aktif: " . $payment['gateway'] . "\n";
    echo "URL Pembayaran: " . $payment['payment_url'] . "\n";

    // Pada aplikasi web:
    // header('Location: ' . $payment['payment_url']);
    // exit;

} catch (BiruPayException $e) {
    echo "Gagal: " . $e->getMessage() . "\n";
}
