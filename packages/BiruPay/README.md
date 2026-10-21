# BiruPay PHP SDK

Official PHP SDK untuk integrasi pembayaran terpusat **Biru PayHub** (Midtrans, Duitku, DOKU) untuk seluruh platform ekosistem **Biru Indonesia Creative**.

[![Latest Stable Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://biru.co.id/pembayaran)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D7.4-8892BF.svg)](https://php.net)

---

## 🚀 Fitur Utama
* **Multi-Gateway 1 Pintu:** Cukup 1 akun Midtrans, Duitku, & Doku untuk melayani semua platform Biru Anda.
* **Popup Otomatis:** Mendukung Midtrans Snap & Duitku Pop.
* **Verifikasi Webhook Otomatis:** Validasi signature keamanan HMAC-SHA256 bawaan.
* **Auto-Sync Gateway:** Pengecekan status real-time langsung ke gateway jika masih pending.
* **Siap untuk Framework:** Laravel, CodeIgniter 4, Symfony, WordPress, Yii, & Native PHP.

---

## 📦 Instalasi via Composer

### Cara 1: Menggunakan Custom Repository Biru
Jalankan di terminal proyek Anda:
```bash
composer config repositories.biru composer https://biru.co.id/pembayaran/composer
composer require biru/payhub
```

Atau tambahkan ke file `composer.json` Anda:
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
Lalu jalankan `composer update`.

---

## 💡 Contoh Penggunaan

### 1. Inisialisasi Klien
```php
<?php
require_once 'vendor/autoload.php';

use BiruPay\Client;

$client = new Client('YOUR_API_KEY');
```

### 2. Membuat Tagihan Pembayaran
```php
use BiruPay\Exception\BiruPayException;

try {
    $payment = $client->createPayment([
        'order_id'       => 'INV-' . time(),
        'amount'         => 50000,
        'customer_name'  => 'Budi Santoso',
        'customer_email' => 'budi@example.com',
        'customer_phone' => '081234567890',
        'item_name'      => 'Aktivasi Layanan Biru VIP'
    ]);

    // Alihkan pembeli langsung ke halaman pembayaran:
    header('Location: ' . $payment['payment_url']);
    exit;

} catch (BiruPayException $e) {
    echo "Gagal: " . $e->getMessage();
}
```

### 3. Menerima Webhook Callback (Otomatis Lunas)
```php
<?php
// file: webhook.php di aplikasi Anda
require_once 'vendor/autoload.php';

use BiruPay\Client;
use BiruPay\Exception\BiruPayException;

$client = new Client('YOUR_API_KEY');

try {
    $webhook = $client->verifyWebhook();

    if ($webhook->isSuccess()) {
        $orderId = $webhook->getOrderId();
        $amount  = $webhook->getAmount();
        $metode  = $webhook->getPaymentMethod();

        // 1. Eksekusi pembaruan status pesanan di database Anda menjadi LUNAS
        // markOrderAsPaid($orderId, $metode);

        // 2. Beri respon 200 OK ke Biru PayHub
        $webhook->respondOk();
    }
} catch (BiruPayException $e) {
    http_response_code(400);
    echo $e->getMessage();
}
```

### 4. Pengecekan Status Real-Time
```php
$status = $client->checkStatus('INV-12345');
echo "Status: " . $status['status']; // 'success', 'pending', 'failed'
```

---

## 📄 Lisensi
Dilisensikan di bawah [MIT License](LICENSE) © Biru Indonesia Creative.
