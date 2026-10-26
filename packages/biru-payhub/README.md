# Biru PayHub - Panduan Pengguna

> Sistem pembayaran terpusat untuk menghubungkan berbagai platform dan aplikasi Biru ke berbagai payment gateway (Midtrans, Duitku, DOKU) hanya dengan **1 akun merchant**.

---

## Alamat Akses

- **Dashboard Admin:** [https://biru.co.id/pembayaran](https://biru.co.id/pembayaran)
- **Halaman Uji Coba (Demo):** [https://biru.co.id/demo/pembayaran/](https://biru.co.id/demo/pembayaran/)
- **Dokumentasi API Interaktif:** [https://biru.co.id/pembayaran/docs](https://biru.co.id/pembayaran/docs)

---

## 3 Langkah Mudah Memulai

### 1. Hubungkan Payment Gateway
1. Buka menu **Setup Gateway** di dashboard.
2. Masukkan kredensial gateway yang Anda miliki:
   - **Duitku:** Merchant Code & API Key.
   - **Midtrans:** Merchant ID, Client Key, & Server Key.
   - **DOKU:** Client ID & Secret Key.
3. Anda dapat mengisi kredensial **Sandbox (Uji Coba)** dan **Live (Produksi)** sekaligus.
4. Salin URL Webhook yang tertera di halaman tersebut dan tempelkan pada dashboard merchant masing-masing gateway.

---

### 2. Daftarkan Platform / Aplikasi Anda
1. Buka menu **Platform & Aplikasi** lalu klik **Tambah Platform**.
2. Isi nama platform (misal: *Biru Store*, *Biru Academy*).
3. Atur preferensi transaksi:
   - **Mode Transaksi:** Pilih **SANDBOX** (uji coba) atau **LIVE** (uang nyata).
   - **Gateway Live:** Gateway yang aktif saat mode Live (misal: Midtrans).
   - **Gateway Sandbox:** Gateway yang aktif saat mode Sandbox (misal: Duitku).
4. Klik **Simpan**. Sistem otomatis membuatkan **API Key** unik untuk platform Anda.

---

### 3. Pasang Pembayaran di Aplikasi Anda

Pilih salah satu cara integrasi yang paling cocok untuk aplikasi Anda:

#### Pilihan A: Menggunakan JavaScript Popup (Paling Mudah)
Cukup pasang script di website Anda:

```html
<!-- 1. Muat Script BiruPay -->
<script src="https://biru.co.id/pembayaran/birupay.js"></script>

<!-- 2. Panggil Pembayaran -->
<script>
BiruPay.pay({
    apiKey: 'MASUKKAN_API_KEY_PLATFORM_ANDA',
    orderId: 'INV-001',
    amount: 50000,
    customerName: 'Budi Santoso',
    customerEmail: 'budi@example.com',
    customerPhone: '081234567890',
    onSuccess: function(result) {
        alert('Pembayaran berhasil!');
        window.location.reload();
    },
    onPending: function(result) {
        alert('Menunggu pembayaran...');
    },
    onError: function(err) {
        alert('Terjadi kesalahan: ' + err.message);
    }
});
</script>
```

#### Pilihan B: Menggunakan PHP / Composer
Jika menggunakan PHP, instal SDK resmi:

```bash
composer require biru/payhub
```

Lalu panggil di backend Anda:

```php
use BiruPay\Client;

$client = new Client('MASUKKAN_API_KEY_PLATFORM_ANDA');

$invoice = $client->createPayment([
    'order_id'       => 'INV-' . time(),
    'amount'         => 50000,
    'customer_name'  => 'Budi Santoso',
    'customer_email' => 'budi@example.com',
    'customer_phone' => '081234567890',
    'item_name'      => 'Langganan Layanan'
]);

// Arahkan pelanggan ke halaman pembayaran:
header('Location: ' . $invoice['payment_url']);
exit;
```

#### Pilihan C: Menggunakan REST API Langsung
Kirim permintaan `POST` ke endpoint:

`POST https://biru.co.id/pembayaran/api/v1/payment/create`

**Header:**
```http
Content-Type: application/json
X-Biru-ApiKey: MASUKKAN_API_KEY_PLATFORM_ANDA
```

**Body JSON:**
```json
{
  "order_id": "INV-20261027-01",
  "amount": 50000,
  "customer_name": "Budi Santoso",
  "customer_email": "budi@example.com",
  "customer_phone": "081234567890",
  "item_name": "Aktivasi Layanan"
}
```

> **Catatan Mode:** Jika parameter `mode` tidak dikirim oleh client, sistem otomatis mengikuti mode yang diatur pada platform Anda (default: `sandbox`). Untuk memaksa mode tertentu dari kode, tambahkan `"mode": "live"` atau `"mode": "sandbox"`.

---

## Fitur Tambahan di Dashboard

- **Ganti Mode Instan:** Anda dapat beralih antara Mode Sandbox dan Live hanya dengan 1 klik pada tabel platform.
- **Terapkan Masal:** Ubah gateway atau mode untuk seluruh platform secara serentak melalui tombol *Mode Masal* atau *Gateway Masal*.
- **Laporan Transaksi:** Pantau status transaksi secara real-time, ekspor data ke Excel/CSV, atau kirim ulang webhook jika notifikasi di aplikasi tujuan sempat tertunda.
- **Notifikasi Otomatis:** Atur notifikasi Telegram Bot dan Email bukti bayar otomatis di menu **Notifikasi & Setup**.

---

&copy; Biru Indonesia Creative &bull; [biru.co.id](https://biru.co.id)
