# Biru PayHub - Sistem Pembayaran Terpusat
**Biru Indonesia Creative**  
URL: `https://biru.co.id/pembayaran`

Sistem gateway pembayaran terpusat untuk seluruh platform, aplikasi web, dan mobile milik Biru Indonesia Creative menggunakan **1 Akun & 1 Project** pada masing-masing Payment Gateway:
- **Midtrans** (Metode Popup Snap)
- **Duitku** (Metode Pop Modal)
- **DOKU** (Metode Checkout / Popup)

---

## Fitur Utama

1. **Pintu Pembayaran Terpusat (One Payment Hub):**
   - Satu endpoint checkout seragam di `biru.co.id/pembayaran/pay`.
   - Menggunakan 1 akun merchant Midtrans, 1 akun Duitku, dan 1 akun DOKU untuk melayani banyak aplikasi/platform Biru.
2. **Identifikasi Platform Otomatis:**
   - Setiap platform Biru memiliki `app_code` (prefix transaksi unik) dan `api_key`.
   - PayHub menandai transaksi, mengarahkan callback/webhook ke platform terkait, dan redirect pelanggan ke `return_url` masing-masing platform.
3. **Metode Popup Snap & Pop:**
   - Dukungan popup snap modal Midtrans (`window.snap.pay`).
   - Dukungan popup modal Duitku Pop (`checkout.process`).
   - Dukungan modal / hosted checkout DOKU.
4. **Dashboard Setup Payment Gateway (Dual Environment Sandbox & Live):**
   - Mendukung penyimpanan kredensial **Sandbox (Testing)** dan **Live (Production)** secara bersamaan untuk Midtrans, Duitku, dan DOKU.
   - Dilengkapi toggle instan untuk beralih antara Mode Sandbox dan Mode Live tanpa perlu copy-paste ulang API Key.
   - Tombol 1-klik salin URL Webhook / Callback untuk dipasang di portal merchant.
5. **Halaman Pengujian & Demo Pembayaran (`/demo/pembayaran`):**
   - Terletak di `../demo/pembayaran` (`https://biru.co.id/demo/pembayaran/`).
   - Memungkinkan input nominal dinamis (bebas / preset Rp 10.000 s/d Rp 1.000.000).
   - Pengujian popup modal Snap (Midtrans), Pop (Duitku), maupun redirect ke Biru PayHub.
   - Konsol debug request & response JSON serta live webhook callback monitor.
5. **Manajemen Platform & Aplikasi Biru:**
   - Pendaftaran aplikasi baru dengan API Key unik dan URL Webhook.
   - Monitoring total transaksi per platform.
6. **Laporan Transaksi:**
   - Filter berdasarkan Platform, Gateway, Status, Rentang Tanggal, dan Pencarian teks.
   - Detail transaksi lengkap dengan raw webhook payload dan status penerusan webhook.
   - Ekspor data laporan transaksi ke format CSV / Excel.
   - Tombol kirim ulang webhook dan manual settlement.
7. **Notifikasi Real-time Transaksi Sukses:**
   - **Telegram:** Notifikasi instan dengan detail nominal, order ID, gateway, nama platform, dan pelanggan via Telegram Bot API.
   - **Email:** Bukti pembayaran otomatis terkirim ke email pelanggan dan salinan ke email tim finance/admin via SMTP / PHP mail.
8. **User Manager:**
   - Manajemen akun administrator dan operator dengan enkripsi password `bcrypt`.
9. **Panduan Integrasi Developer (Interactive API Docs):**
   - Contoh kode siap pakai dalam bahasa PHP, JavaScript, dan cURL untuk tim developer platform Biru.

---

## Struktur Folder

```
pembayaran/
├── .htaccess                     # Routing Apache & proteksi file sensitif
├── index.php                     # Single entry point
├── migrate.php                   # CLI migration runner
├── README.md                     # Dokumentasi teknis
├── app/
│   ├── autoload.php              # PSR-4 Autoloader
│   ├── Controllers/              # Controller MVC
│   │   ├── ApiController.php
│   │   ├── ApplicationController.php
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── DocsController.php
│   │   ├── GatewayController.php
│   │   ├── InstallController.php
│   │   ├── PaymentController.php
│   │   ├── SettingController.php
│   │   ├── TransactionController.php
│   │   ├── UserController.php
│   │   └── WebhookController.php
│   ├── Core/                     # Core framework engine
│   │   ├── Controller.php
│   │   ├── Database.php
│   │   ├── Model.php
│   │   ├── Request.php
│   │   ├── Response.php
│   │   ├── Router.php
│   │   ├── Session.php
│   │   └── View.php
│   ├── Gateways/                 # Payment Gateway Drivers
│   │   ├── DokuGateway.php
│   │   ├── DuitkuGateway.php
│   │   ├── MidtransGateway.php
│   │   └── PaymentGatewayInterface.php
│   ├── Models/                   # Data Models
│   │   ├── Application.php
│   │   ├── GatewayConfig.php
│   │   ├── Log.php
│   │   ├── Setting.php
│   │   ├── Transaction.php
│   │   └── User.php
│   └── Services/                 # Business Services
│       ├── EmailService.php
│       ├── TelegramService.php
│       └── WebhookDispatcherService.php
├── config/
│   ├── app.php                   # Pengaturan aplikasi & base URL
│   └── database.php              # Pengaturan koneksi MySQL / .env loader
├── database/
│   ├── schema.sql                # Skema tabel MySQL
│   └── seed.sql                  # Data awal default
├── public/
│   └── assets/                   # Aset Bootstrap 5, jQuery, Icons, CSS, JS
│       ├── css/
│       └── js/
└── views/                        # Template Blade/PHP views
    ├── applications/
    ├── auth/
    ├── dashboard/
    ├── docs/
    ├── gateways/
    ├── install/
    ├── layouts/
    ├── payment/
    ├── settings/
    ├── transactions/
    └── users/
```

---

## Cara Instalasi & Menjalankan

1. **Akses Web Installer:**
   - Buka browser ke: `https://biru.co.id/pembayaran`
   - Jika database belum terkonfigurasi, sistem otomatis menampilkan halaman **Konfigurasi Database**.
   - Masukkan informasi Host, Port, Nama Database MySQL, Username, dan Password, lalu klik **Simpan & Pasang Database**.

2. **Atau melalui Terminal (CLI):**
   ```bash
   cd /home/biru/domains/biru.co.id/public_html/pembayaran
   # Buat file .env atau edit config/database.php
   php migrate.php
   ```

3. **Login Akun Admin Default:**
   - **Username:** `admin`
   - **Password:** `Password123!`
   - Segera ubah password setelah login melalui menu **User Manager**.

---

## Pengaturan Webhook di Payment Gateway

Daftarkan URL callback berikut di masing-masing portal payment gateway:
- **Midtrans:** `https://biru.co.id/pembayaran/webhook/midtrans`
- **Duitku:** `https://biru.co.id/pembayaran/webhook/duitku`
- **DOKU:** `https://biru.co.id/pembayaran/webhook/doku`

---

## Contoh Integrasi untuk Aplikasi Biru

### 1. Membuat Pembayaran (POST API)
```php
$payload = [
    'order_id'       => 'INV-1002',
    'amount'         => 150000,
    'gateway'        => 'midtrans', // atau 'duitku' / 'doku'
    'customer_name'  => 'Pelanggan Biru',
    'customer_email' => 'pelanggan@example.com'
];

$ch = curl_init('https://biru.co.id/pembayaran/api/v1/payment/create');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-Biru-ApiKey: YOUR_PLATFORM_API_KEY'
]);

$response = json_decode(curl_exec($ch), true);
// Redirect pelanggan ke: $response['data']['payment_url']
```

