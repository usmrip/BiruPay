<?php
use App\Core\View;
$isEdit = !empty($application);
$action = $isEdit ? View::url('applications/' . $application['id']) : View::url('applications');
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card-custom">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold"><?= $isEdit ? 'Edit Platform / Aplikasi' : 'Tambah Platform / Aplikasi Baru' ?></h6>
            </div>
            <div class="card-body">
                <form action="<?= $action ?>" method="POST">
                    <?= View::csrfField() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Kode Platform (Prefix Unik)</label>
                            <input type="text" name="app_code" class="form-control font-monospace" 
                                   value="<?= View::escape($application['app_code'] ?? '') ?>" 
                                   placeholder="Contoh: BIRU-STORE, BIRU-ACADEMY" 
                                   <?= $isEdit ? 'readonly' : 'required' ?>>
                            <small class="text-muted">Digunakan sebagai awalan kode referensi transaksi (Hanya huruf, angka, minus)</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Platform / Aplikasi</label>
                            <input type="text" name="app_name" class="form-control" 
                                   value="<?= View::escape($application['app_name'] ?? '') ?>" 
                                   placeholder="Contoh: Biru Online Store" required>
                        </div>
                    </div>

                    <?php if (!$isEdit): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">API Key Rahasia (Auto-generated)</label>
                            <div class="input-group">
                                <input type="text" name="api_key" class="form-control font-monospace" value="<?= View::escape($defaultApiKey ?? '') ?>" readonly>
                                <button class="btn btn-outline-secondary btn-copy-clipboard" type="button" data-clipboard-text="<?= View::escape($defaultApiKey ?? '') ?>">
                                    <i class="bi bi-clipboard"></i> Salin
                                </button>
                            </div>
                            <small class="text-muted">API Key digunakan oleh aplikasi pemanggil untuk autentikasi endpoint pembuatan pembayaran</small>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payment Gateway Platform Ini</label>
                        <select name="default_gateway" class="form-select">
                            <option value="default" <?= ($application['default_gateway'] ?? 'default') === 'default' ? 'selected' : '' ?>>
                                🌐 Gunakan Gateway Default Sistem (Saat ini: <?= strtoupper($globalDefaultGateway ?? 'DUITKU') ?>)
                            </option>
                            <option value="duitku" <?= ($application['default_gateway'] ?? '') === 'duitku' ? 'selected' : '' ?>>
                                💳 Duitku (Duitku Pop Modal)
                            </option>
                            <option value="midtrans" <?= ($application['default_gateway'] ?? '') === 'midtrans' ? 'selected' : '' ?>>
                                💳 Midtrans (Snap Popup)
                            </option>
                            <option value="doku" <?= ($application['default_gateway'] ?? '') === 'doku' ? 'selected' : '' ?>>
                                💳 DOKU Checkout
                            </option>
                        </select>
                        <small class="text-muted">Pilih gateway khusus untuk platform ini, atau biarkan "Default Sistem" agar otomatis mengikuti konfigurasi gateway utama.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Zona Waktu Platform (Timezone)</label>
                        <select name="timezone" class="form-select">
                            <?php 
                            $currentTimezone = $application['timezone'] ?? 'UTC';
                            foreach (View::getTimezones() as $tzKey => $tzLabel): 
                            ?>
                                <option value="<?= $tzKey ?>" <?= $currentTimezone === $tzKey ? 'selected' : '' ?>>
                                    <?= $tzLabel ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Digunakan sebagai acuan tampilan waktu transaksi dan pelunasan pada invoice platform ini (Default: UTC)</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Webhook Callback URL Aplikasi</label>
                        <input type="url" name="webhook_url" class="form-control font-monospace" 
                               value="<?= View::escape($application['webhook_url'] ?? '') ?>" 
                               placeholder="https://aplikasi-biru.co.id/api/payment-callback">
                        <small class="text-muted">PayHub akan meneruskan notifikasi pembayaran sukses ke URL ini dengan signature HMAC-SHA256</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Return URL Default (Redirect Setelah Pembayaran)</label>
                        <input type="url" name="return_url" class="form-control font-monospace" 
                               value="<?= View::escape($application['return_url'] ?? '') ?>" 
                               placeholder="https://aplikasi-biru.co.id/pesanan/selesai">
                        <small class="text-muted">Halaman di aplikasi Anda yang dituju pelanggan setelah pembayaran selesai</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Deskripsi Layanan (Opsional)</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Keterangan singkat aplikasi ini..."><?= View::escape($application['description'] ?? '') ?></textarea>
                    </div>

                    <?php if ($isEdit): ?>
                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Status</label>
                            <select name="status" class="form-select">
                                <option value="active" <?= ($application['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif (Dapat menerima pembayaran)</option>
                                <option value="inactive" <?= ($application['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Nonaktif (Akses ditolak)</option>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between">
                        <a href="<?= View::url('applications') ?>" class="btn btn-light border">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Perbarui Platform' : 'Simpan Platform' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

