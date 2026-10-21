<?php
use App\Core\View;
$defaultApp = !empty($applications) ? $applications[0] : null;
?>

<div class="row g-4">
    <!-- Sidebar Navigation within Docs -->
    <div class="col-lg-3">
        <div class="card-custom sticky-top" style="top: 20px;">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold"><i class="bi bi-book me-1 text-primary"></i> Daftar Panduan</h6>
            </div>
            <div class="list-group list-group-flush small">
                <a href="#solusi-cepat" class="list-group-item list-group-item-action fw-bold text-primary">
                    <i class="bi bi-lightning-charge-fill me-1 text-warning"></i> Solusi Instan (formulir.vip)
                </a>
                <a href="#composer-package" class="list-group-item list-group-item-action fw-bold text-success">
                    <i class="bi bi-box-seam me-1 text-success"></i> Composer Package (biru/payhub)
                </a>
                <a href="#js-sdk" class="list-group-item list-group-item-action">
                    <i class="bi bi-filetype-js me-1 text-warning"></i> JavaScript SDK (birupay.js)
                </a>
                <a href="#php-sdk" class="list-group-item list-group-item-action">
                    <i class="bi bi-filetype-php me-1 text-info"></i> PHP SDK (BiruPay.php)
                </a>
                <a href="#konsep" class="list-group-item list-group-item-action">1. Konsep & Arsitektur</a>
                <a href="#api-create" class="list-group-item list-group-item-action">2. REST API: Buat Pembayaran</a>
                <a href="#webhook-handling" class="list-group-item list-group-item-action">3. Menerima Webhook Callback</a>
                <a href="#status-check" class="list-group-item list-group-item-action">4. Cek Status Transaksi</a>
            </div>
            <div class="p-3 bg-light border-top">
                <small class="text-muted d-block mb-2">SDK Siap Pakai:</small>
                <a href="<?= $baseUrl ?>/sdk/BiruPay.php" class="btn btn-sm btn-outline-dark w-100 mb-2">
                    <i class="bi bi-download me-1"></i> Unduh BiruPay.php
                </a>
                <a href="<?= $baseUrl ?>/birupay.js" target="_blank" class="btn btn-sm btn-outline-primary w-100">
                    <i class="bi bi-code-slash me-1"></i> Buka birupay.js
                </a>
            </div>
        </div>
    </div>

    <div class="col-lg-9">
        <!-- ============================================================== -->
        <!-- FEATURED HERO: SOLUSI INSTAN UNTUK FORMULIR.VIP & PLATFORM LAIN -->
        <!-- ============================================================== -->
        <div class="card-custom mb-4 border-primary border-opacity-50" id="solusi-cepat">
            <div class="card-header text-white" style="background: linear-gradient(135deg, #0052cc, #003e9c);">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-warning text-dark fw-bold mb-1"><i class="bi bi-stars"></i> Rekomendasi Tercepat</span>
                        <h5 class="mb-0 fw-bold">Solusi Integrasi Mudah: Pasang Pembayaran di formulir.vip / Web Form</h5>
                    </div>
                    <span class="fs-2 opacity-50"><i class="bi bi-plug"></i></span>
                </div>
            </div>
            <div class="card-body p-4">
                <p class="text-muted mb-3">
                    Untuk aplikasi seperti <strong>formulir.vip</strong>, landing page, atau website form lainnya, Anda <strong>tidak perlu lagi menulis puluhan baris backend cURL atau popup Midtrans/Duitku yang rumit</strong>. Kami telah menyiapkan <strong>BiruPay Drop-in SDK</strong> yang bekerja secara <em>Plug & Play</em>.
                </p>

                <!-- Interactive Platform Picker -->
                <div class="p-3 bg-light rounded-3 border mb-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold text-dark mb-1">Pilih Platform Anda untuk Mengisi API Key Otomatis:</label>
                            <select id="doc-app-selector" class="form-select form-select-sm">
                                <?php if (!empty($applications)): ?>
                                    <?php foreach ($applications as $app): ?>
                                        <option value="<?= View::escape($app['api_key']) ?>" data-code="<?= View::escape($app['app_code']) ?>" data-name="<?= View::escape($app['app_name']) ?>" data-gw="<?= View::escape($app['default_gateway'] ?? 'default') ?>">
                                            <?= View::escape($app['app_name']) ?> (<?= View::escape($app['app_code']) ?>) - Gateway: <?= strtoupper($app['default_gateway'] ?? 'default') ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="YOUR_API_KEY">Default Platform (YOUR_API_KEY)</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-dark mb-1">API Key Terpilih:</label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="doc-api-key-display" class="form-control font-monospace" value="<?= View::escape($defaultApp['api_key'] ?? 'YOUR_API_KEY') ?>" readonly>
                                <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText($('#doc-api-key-display').val()); alert('API Key disalin!');">
                                    <i class="bi bi-clipboard"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs of Solutions -->
                <ul class="nav nav-pills mb-3 gap-2" id="solutionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold small" id="tab-form-tab" data-bs-toggle="pill" data-bs-target="#tab-form" type="button">
                            <i class="bi bi-ui-checks me-1"></i> Opsi 1: Tempel di Formulir (1 Baris JS)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold small" id="tab-button-tab" data-bs-toggle="pill" data-bs-target="#tab-button" type="button">
                            <i class="bi bi-hand-index-thumb me-1"></i> Opsi 2: Tombol HTML Langsung (Zero-JS)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold small" id="tab-composer-tab" data-bs-toggle="pill" data-bs-target="#tab-composer" type="button">
                            <i class="bi bi-box-seam me-1"></i> Opsi 3: Composer (biru/payhub)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold small" id="tab-php-tab" data-bs-toggle="pill" data-bs-target="#tab-php" type="button">
                            <i class="bi bi-filetype-php me-1"></i> Opsi 4: Single-File PHP (BiruPay.php)
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="solutionTabsContent">
                    <!-- TAB 1: FORM BINDER -->
                    <div class="tab-pane fade show active" id="tab-form" role="tabpanel">
                        <div class="border rounded-3 p-3 bg-white mb-3">
                            <h6 class="fw-bold text-dark"><i class="bi bi-check2-circle text-success me-1"></i> Cocok untuk formulir.vip:</h6>
                            <p class="small text-muted mb-2">
                                Cukup masukkan skrip <code>birupay.js</code> dan panggil <code>BiruPay.bindForm()</code>. Form pendaftaran Anda secara otomatis akan memunculkan popup pembayaran (Midtrans Snap / Duitku Pop sesuai setting platform Anda di PayHub) saat tombol kirim ditekan!
                            </p>
                            <div class="position-relative">
                                <pre class="code-box mb-0" id="code-form-example">&lt;!-- 1. Sertakan SDK BiruPay di halaman formulir Anda --&gt;
&lt;script src="<?= $baseUrl ?>/birupay.js"&gt;&lt;/script&gt;

&lt;!-- 2. Contoh Formulir Anda di formulir.vip --&gt;
&lt;form id="form-pendaftaran" action="/sukses.php" method="POST"&gt;
    &lt;input type="text" name="nama" placeholder="Nama Lengkap" required&gt;
    &lt;input type="email" name="email" placeholder="Email" required&gt;
    &lt;input type="tel" name="telepon" placeholder="Nomor WhatsApp" required&gt;
    &lt;input type="hidden" name="nominal" value="50000"&gt;

    &lt;button type="submit" class="btn btn-primary"&gt;
        Kirim Formulir &amp; Bayar Rp 50.000
    &lt;/button&gt;
&lt;/form&gt;

&lt;!-- 3. Kunci Formulir dengan BiruPay (Hanya 6 Baris!) --&gt;
&lt;script&gt;
BiruPay.bindForm('#form-pendaftaran', {
    apiKey: '<span class="selected-key-placeholder"><?= View::escape($defaultApp['api_key'] ?? 'YOUR_API_KEY') ?></span>',
    amount: 50000, // Atau biarkan kosong jika ada input name="nominal"
    itemName: 'Pendaftaran Formulir VIP',
    successUrl: 'https://formulir.vip/selesai.php' // URL setelah lunas
});
&lt;/script&gt;</pre>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: ZERO-JS BUTTON -->
                    <div class="tab-pane fade" id="tab-button" role="tabpanel">
                        <div class="border rounded-3 p-3 bg-white mb-3">
                            <h6 class="fw-bold text-dark"><i class="bi bi-check2-circle text-success me-1"></i> Tombol HTML Tanpa Menulis JavaScript:</h6>
                            <p class="small text-muted mb-2">
                                Cukup beri atribut <code>data-birupay-button</code> pada tombol apapun di halaman web Anda:
                            </p>
                            <pre class="code-box mb-0">&lt;!-- 1. Muat Script SDK --&gt;
&lt;script src="<?= $baseUrl ?>/birupay.js"&gt;&lt;/script&gt;

&lt;!-- 2. Pasang Tombol dengan Data Attributes --&gt;
&lt;button 
    data-birupay-button
    data-api-key="<span class="selected-key-placeholder"><?= View::escape($defaultApp['api_key'] ?? 'YOUR_API_KEY') ?></span>"
    data-amount="100000"
    data-item-name="Paket VIP Member"
    data-customer-name="Budi Santoso"
    data-customer-email="budi@example.com"
    data-customer-phone="081234567890"
    data-success-url="https://formulir.vip/selesai.php"
    class="btn btn-success py-2 px-4 fw-bold"&gt;
    &lt;i class="bi bi-shield-lock"&gt;&lt;/i&gt; Bayar Sekarang (Rp 100.000)
&lt;/button&gt;</pre>
                        </div>
                    </div>

                    <!-- TAB 3: COMPOSER PACKAGE -->
                    <div class="tab-pane fade" id="tab-composer" role="tabpanel">
                        <div class="border rounded-3 p-3 bg-white mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-box-seam text-success me-1"></i> Instalasi via Composer (Laravel, CodeIgniter 4, WordPress, Native):</h6>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Package: biru/payhub</span>
                            </div>
                            <p class="small text-muted mb-2">
                                Anda dapat menginstal package <code>biru/payhub</code> langsung ke proyek PHP Anda menggunakan Composer:
                            </p>
                            
                            <h6 class="small fw-bold text-dark mt-3 mb-1">Langkah 1: Tambahkan Custom Repository Biru di Terminal Proyek Anda:</h6>
                            <pre class="code-box mb-3">composer config repositories.biru composer <?= $baseUrl ?>/composer
composer require biru/payhub</pre>

                            <p class="small text-muted mb-1">Atau tambahkan manual ke <code>composer.json</code> Anda:</p>
                            <pre class="code-box mb-3">{
    "repositories": [
        {
            "type": "composer",
            "url": "<?= $baseUrl ?>/composer"
        }
    ],
    "require": {
        "biru/payhub": "^1.0"
    }
}</pre>

                            <h6 class="small fw-bold text-dark mb-1">Langkah 2: Gunakan di Kode PHP Anda:</h6>
                            <pre class="code-box mb-0">&lt;?php
require_once 'vendor/autoload.php';

use BiruPay\Client;

$pay = new Client('<span class="selected-key-placeholder"><?= View::escape($defaultApp['api_key'] ?? 'YOUR_API_KEY') ?></span>');

// Buat tagihan
$payment = $pay->createPayment([
    'order_id'       => 'INV-' . time(),
    'amount'         => 50000,
    'customer_name'  => 'Budi Santoso',
    'customer_email' => 'budi@example.com',
    'customer_phone' => '081234567890',
    'item_name'      => 'Pembelian Produk Layanan Biru'
]);

// Redirect ke URL pembayaran:
header('Location: ' . $payment['payment_url']);
exit;
?&gt;</pre>
                        </div>
                    </div>

                    <!-- TAB 4: SINGLE-FILE PHP SDK -->
                    <div class="tab-pane fade" id="tab-php" role="tabpanel">
                        <div class="border rounded-3 p-3 bg-white mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-check2-circle text-success me-1"></i> Integrasi Server-to-Server dengan BiruPay.php:</h6>
                                <a href="<?= $baseUrl ?>/sdk/BiruPay.php" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download me-1"></i> Unduh File BiruPay.php
                                </a>
                            </div>
                            <p class="small text-muted mb-2">
                                Letakkan file <code>BiruPay.php</code> di folder proyek Anda (Native PHP, Laravel, CodeIgniter, WordPress):
                            </p>
                            <pre class="code-box mb-0">&lt;?php
require_once 'BiruPay.php';

$pay = new BiruPay('<span class="selected-key-placeholder"><?= View::escape($defaultApp['api_key'] ?? 'YOUR_API_KEY') ?></span>');

// 1. Buat Permintaan Pembayaran
$result = $pay->createPayment([
    'order_id'       => 'VIP-' . time(),
    'amount'         => 75000,
    'customer_name'  => $_POST['nama'] ?? 'Pelanggan',
    'customer_email' => $_POST['email'] ?? 'pelanggan@biru.co.id',
    'customer_phone' => $_POST['hp'] ?? '08123456789',
    'item_name'      => 'Biaya Registrasi Formulir VIP'
]);

if ($result['status'] === 'success') {
    $data = $result['data'];
    // $data['payment_url']         -> URL Checkout Biru
    // $data['snap_token']          -> Token Midtrans Snap (jika gateway midtrans)
    // $data['gateway_reference']   -> Ref Duitku Pop (jika gateway duitku)

    // Redirect pelanggan langsung ke URL pembayaran:
    header('Location: ' . $data['payment_url']);
    exit;
} else {
    echo 'Gagal: ' . $result['message'];
}
?&gt;</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- COMPOSER PACKAGE (biru/payhub)                                 -->
        <!-- ============================================================== -->
        <div class="card-custom mb-4" id="composer-package">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded bg-success-subtle text-success fs-5"><i class="bi bi-box-seam"></i></span>
                    <div>
                        <h6 class="mb-0 fw-bold">Distribusi Resmi via Composer (<code class="text-dark">biru/payhub</code>)</h6>
                        <small class="text-muted">Package resmi berstandar PSR-4 untuk PHP Framework &amp; Native</small>
                    </div>
                </div>
                <span class="badge bg-success">v1.0.0</span>
            </div>
            <div class="card-body p-4">
                <p class="small text-muted mb-3">
                    Package Composer <strong><code>biru/payhub</code></strong> telah di-deploy dan dapat diinstal di proyek PHP apapun (Laravel, Symfony, CodeIgniter 4, WordPress, Yii, maupun native PHP).
                </p>

                <h6 class="small fw-bold text-dark mb-1">A. Instalasi Cepat via Terminal:</h6>
                <pre class="code-box mb-3">composer config repositories.biru composer <?= $baseUrl ?>/composer
composer require biru/payhub</pre>

                <h6 class="small fw-bold text-dark mb-1">B. Struktur Class &amp; Namespace:</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Class</th>
                                <th>Deskripsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>\BiruPay\Client</code></td>
                                <td>Klien utama untuk memanggil API PayHub (<code>createPayment</code>, <code>checkStatus</code>, dll.)</td>
                            </tr>
                            <tr>
                                <td><code>\BiruPay\Webhook</code></td>
                                <td>Parser &amp; verifikator signature HMAC-SHA256 untuk webhook notifikasi pembayaran</td>
                            </tr>
                            <tr>
                                <td><code>\BiruPay\Exception\BiruPayException</code></td>
                                <td>Class exception untuk menangani error transaksi dan HTTP response</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="small fw-bold text-dark mb-1">C. Contoh Lengkap di Controller / Script:</h6>
                <pre class="code-box mb-0">&lt;?php
use BiruPay\Client;
use BiruPay\Exception\BiruPayException;

$client = new Client('<span class="selected-key-placeholder"><?= View::escape($defaultApp['api_key'] ?? 'YOUR_API_KEY') ?></span>');

// 1. Buat transaksi
try {
    $payment = $client->createPayment([
        'order_id'       => 'ORDER-' . time(),
        'amount'         => 100000,
        'customer_name'  => 'Pelanggan Biru',
        'customer_email' => 'pelanggan@biru.co.id',
        'customer_phone' => '081234567890',
        'item_name'      => 'Aktivasi Layanan'
    ]);

    // Ambil URL checkout atau token
    $checkoutUrl = $payment['payment_url'];
    header('Location: ' . $checkoutUrl);
    exit;

} catch (BiruPayException $e) {
    echo 'Error: ' . $e->getMessage();
}

// 2. Di file webhook / callback controller:
try {
    $webhook = $client->verifyWebhook();
    if ($webhook->isSuccess()) {
        $orderId = $webhook->getOrderId();
        $amount  = $webhook->getAmount();
        // Lakukan aktivasi pesanan...
        $webhook->respondOk();
    }
} catch (BiruPayException $e) {
    http_response_code(400);
}
?&gt;</pre>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 1. KONSEP & ARSITEKTUR                                         -->
        <!-- ============================================================== -->
        <div class="card-custom mb-4" id="konsep">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">1. Konsep & Arsitektur Sistem Terpusat</h6>
            </div>
            <div class="card-body">
                <p>
                    <strong>Biru PayHub</strong> adalah gateway pembayaran terpusat untuk seluruh platform dan aplikasi milik <strong>Biru Indonesia Creative</strong>.
                </p>
                <ul>
                    <li><strong>1 Akun Payment Gateway:</strong> Cukup daftarkan 1 akun di Midtrans, Duitku, dan Doku.com untuk melayani puluhan platform Biru.</li>
                    <li><strong>Banyak Platform / Aplikasi:</strong> Setiap aplikasi Biru didaftarkan di menu <a href="<?= $baseUrl ?>/applications">Platform & Aplikasi</a> dan memperoleh <code>app_code</code> dan <code>api_key</code>.</li>
                    <li><strong>Gateway Fleksibel:</strong> Setiap platform bisa disetel memilih gateway sendiri (misal: formulir.vip pakai Duitku, Biru Academy pakai Midtrans), atau diubah masal sekaligus.</li>
                    <li><strong>Identifikasi Otomatis:</strong> PayHub otomatis menandai transaksi, mengirim notifikasi instan Telegram & Email, lalu meneruskan webhook callback ke aplikasi tujuan.</li>
                </ul>

                <div class="p-3 bg-light rounded-3 border mb-3">
                    <div class="small fw-bold text-secondary mb-1">Base URL Endpoint API:</div>
                    <div class="font-monospace text-primary fw-bold"><?= $baseUrl ?>/api/v1</div>
                </div>

                <div class="alert alert-info d-flex align-items-center justify-content-between mb-0">
                    <div>
                        <i class="bi bi-play-circle-fill fs-5 me-2"></i>
                        <strong>Ingin mencoba transaksi nyata?</strong> Kunjungi halaman demo pengujian pembayaran.
                    </div>
                    <a href="/demo/pembayaran" target="_blank" class="btn btn-sm btn-primary">
                        Buka Demo Pembayaran &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 2. MEMBUAT PEMBAYARAN VIA RAW REST API                         -->
        <!-- ============================================================== -->
        <div class="card-custom mb-4" id="api-create">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">2. Endpoint: Membuat Permintaan Pembayaran (REST API)</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Jika tidak ingin menggunakan SDK, aplikasi klien Anda dapat melakukan direct HTTP POST ke endpoint ini.
                </p>

                <div class="mb-3">
                    <span class="badge bg-success me-2">POST</span>
                    <code class="text-dark fw-bold"><?= $baseUrl ?>/api/v1/payment/create</code>
                </div>

                <div class="mb-3">
                    <h6 class="small fw-bold">Header Wajib:</h6>
                    <pre class="code-box mb-0">X-Biru-ApiKey: <span class="selected-key-placeholder"><?= View::escape($defaultApp['api_key'] ?? 'YOUR_PLATFORM_API_KEY') ?></span>
Content-Type: application/json</pre>
                </div>

                <div class="mb-3">
                    <h6 class="small fw-bold">Contoh JSON Request Payload:</h6>
                    <pre class="code-box mb-0">{
  "order_id": "BACAD-20261021-001",
  "amount": 150000,
  "gateway": "duitku", // Opsional: jika kosong, otomatis mengikuti gateway platform Anda di PayHub
  "customer_name": "Budi Santoso",
  "customer_email": "budi@example.com",
  "customer_phone": "081234567890",
  "item_name": "Kursus Fullstack Web Developer",
  "item_details": [
    {
      "id": "COURSE-01",
      "price": 150000,
      "quantity": 1,
      "name": "Kursus Fullstack Web Developer"
    }
  ]
}</pre>
                </div>

                <div class="mb-3">
                    <h6 class="small fw-bold">Contoh Response JSON dari Biru PayHub:</h6>
                    <pre class="code-box mb-0">{
  "status": "success",
  "message": "Transaksi pembayaran berhasil dibuat.",
  "data": {
    "trx_reference": "BIRU-20261021-A9F3",
    "order_id": "BACAD-20261021-001",
    "amount": 150000,
    "gateway": "duitku",
    "mode": "production",
    "script_url": "https://app-prod.duitku.com/lib/js/duitku.js",
    "payment_url": "<?= $baseUrl ?>/pay?ref=BIRU-20261021-A9F3",
    "gateway_payment_url": "https://app-prod.duitku.com/redirect_checkout?reference=D13765...",
    "snap_token": null,
    "gateway_reference": "D1376526MQ8ZYIYK4J31VAP",
    "created_at": "2026-10-21 16:00:00"
  }
}</pre>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 3. MENERIMA WEBHOOK CALLBACK                                   -->
        <!-- ============================================================== -->
        <div class="card-custom mb-4" id="webhook-handling">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">3. Menerima Webhook Callback di Aplikasi Anda</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Saat transaksi dinyatakan <strong>LUNAS (success)</strong>, Biru PayHub akan mengirimkan HTTP POST otomatis ke <code>webhook_url</code> platform Anda disertai header keamanan <code>X-Biru-Signature</code> (HMAC-SHA256).
                </p>

                <h6 class="small fw-bold">Contoh Handler Webhook di Aplikasi Anda (PHP):</h6>
                <pre class="code-box mb-0">&lt;?php
// file: api/payment-callback.php di platform Anda (formulir.vip)

$apiKey = '<span class="selected-key-placeholder"><?= View::escape($defaultApp['api_key'] ?? 'YOUR_PLATFORM_API_KEY') ?></span>';
$rawInput = file_get_contents('php://input');
$signatureHeader = $_SERVER['HTTP_X_BIRU_SIGNATURE'] ?? '';

// 1. Verifikasi Keaslian Signature HMAC-SHA256
$expectedSignature = hash_hmac('sha256', $rawInput, $apiKey);
if (!hash_equals($expectedSignature, $signatureHeader)) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
    exit;
}

// 2. Baca Data Transaksi Lunas
$payload = json_decode($rawInput, true);
$orderId = $payload['data']['order_id'];
$status  = $payload['data']['status']; // 'success', 'pending', 'failed'

if ($status === 'success') {
    // 3. Update status pendaftaran / pesanan di database Anda menjadi LUNAS
    // activateUserVIP($orderId);
}

// Berikan respon HTTP 200 OK ke PayHub
http_response_code(200);
echo json_encode(['status' => 'ok']);
?&gt;</pre>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- 4. CEK STATUS TRANSAKSI                                        -->
        <!-- ============================================================== -->
        <div class="card-custom" id="status-check">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold">4. Endpoint: Cek Status Transaksi</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="badge bg-primary me-2">GET</span>
                    <code class="text-dark fw-bold"><?= $baseUrl ?>/api/v1/payment/status/{order_id}</code>
                </div>
                <p class="small text-muted mb-2">
                    Kirimkan header <code>X-Biru-ApiKey: YOUR_KEY</code> untuk mengambil data status terkini. PayHub otomatis mensinkronkan status ke Midtrans/Duitku jika status transaksi masih pending.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var selector = document.getElementById('doc-app-selector');
    var display = document.getElementById('doc-api-key-display');

    if (selector && display) {
        selector.addEventListener('change', function() {
            var key = this.value;
            display.value = key;

            var placeholders = document.querySelectorAll('.selected-key-placeholder');
            placeholders.forEach(function(el) {
                el.innerText = key;
            });
        });
    }
});
</script>
