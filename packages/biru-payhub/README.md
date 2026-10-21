# Biru PayHub PHP SDK

SDK Resmi PHP untuk integrasi pembayaran terpusat **Biru PayHub** (Midtrans, Duitku, DOKU) untuk seluruh platform ekosistem **Biru Indonesia Creative**.

## 🚀 Instalasi via Composer

### Metode 1: Menggunakan Custom Repository Biru (Langsung tanpa Packagist)
Jalankan di terminal proyek Anda:
```bash
composer config repositories.biru composer https://biru.co.id/pembayaran/composer
composer require biru/payhub
```

Atau tambahkan ke file `composer.json` proyek Anda:
```json
{
    "repositories": [
        {
            "type": "composer",
            "url": "https://biru.co.id/pembayaran/composer"
        }
    ],
    "require": {
        "biru/payhub": "^1.0"
    }
}
```
Lalu jalankan:
```bash
composer update
```

### Metode 2: Menggunakan Git Repository
Jika package ini di-push ke GitHub (`https://github.com/biru-creative/payhub-php-sdk`):
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/biru-creative/payhub-php-sdk"
        }
    ],
    "require": {
        "biru/payhub": "^1.0"
    }
}
```

---

## 💻 Contoh Penggunaan

### 1. Inisialisasi Klien
```php
<?php
require_once 'vendor/autoload.php';

use BiruPay\Client;

$pay = new Client('YOUR_PLATFORM_API_KEY');
```

### 2. Membuat Tagihan Pembayaran
```php
try {
    $payment = $pay->createPayment([
        'order_id'       => 'INV-20261021-001',
        'amount'         => 50000,
        'customer_name'  => 'Budi Santoso',
        'customer_email' => 'budi@example.com',
        'customer_phone' => '081234567890',
        'item_name'      => 'Langganan VIP Member',
        // 'gateway'     => 'duitku' // Opsional: default mengikuti pengaturan di dashboard PayHub
    ]);

    // Data respon:
    $paymentUrl = $payment['payment_url'];        // URL Checkout PayHub
    $snapToken  = $payment['snap_token'];         // Token Midtrans (jika Midtrans)
    $duitkuRef  = $payment['gateway_reference'];  // Reference Duitku (jika Duitku)

    // Redirect pelanggan ke halaman pembayaran:
    header('Location: ' . $paymentUrl);
    exit;

} catch (\BiruPay\Exception\BiruPayException $e) {
    echo "Gagal: " . $e->getMessage();
}
```

### 3. Memverifikasi Webhook Callback
```php
<?php
// file: webhook.php di aplikasi Anda
require_once 'vendor/autoload.php';

use BiruPay\Client;

$pay = new Client('YOUR_PLATFORM_API_KEY');

try {
    $webhook = $pay->verifyWebhook();

    if ($webhook->isSuccess()) {
        $orderId = $webhook->getOrderId();
        $amount  = $webhook->getAmount();
        $method  = $webhook->getPaymentMethod();

        // Update database: Tandai pesanan / formulir lunas!
        // activateVipMember($orderId);

        // Beri respon OK (200) ke PayHub
        $webhook->respondOk();
    }
} catch (\BiruPay\Exception\BiruPayException $e) {
    http_response_code(400);
    echo $e->getMessage();
}
```

### 4. Cek Status Transaksi Real-Time
```php
$status = $pay->checkStatus('INV-20261021-001');
echo "Status: " . $status['status']; // 'success', 'pending', 'failed'
```

---

## 📄 Lisensi
MIT License © Biru Indonesia Creative

