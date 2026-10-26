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

                    <!-- Gateway Configuration per Mode (Sandbox & Live) -->
                    <div class="card bg-light border-0 rounded-3 p-3 mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-toggles text-primary fs-5 me-2"></i>
                            <h6 class="mb-0 fw-bold">Konfigurasi Gateway per Mode Transaksi</h6>
                        </div>
                        <p class="text-muted small mb-3">
                            Tentukan payment gateway yang digunakan saat aplikasi memproses pembayaran pada mode <strong>Live (Production)</strong> atau <strong>Sandbox (Testing)</strong>. Jika client tidak menentukan mode saat request, sistem secara default akan memilih gateway mode <strong>Sandbox</strong>.
                        </p>

                        <div class="row g-3">
                            <!-- Gateway Mode Sandbox -->
                            <div class="col-md-6">
                                <div class="p-3 bg-white border rounded-3 h-100 shadow-sm">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label small fw-bold mb-0 text-warning-emphasis">
                                            <i class="bi bi-bug me-1 text-warning"></i> Gateway Sandbox (Testing)
                                        </label>
                                        <span class="badge bg-warning-subtle text-warning-emphasis font-monospace small">Default Client</span>
                                    </div>
                                    <select name="gateway_sandbox" class="form-select form-select-sm mb-2">
                                        <option value="default" <?= ($application['gateway_sandbox'] ?? 'default') === 'default' ? 'selected' : '' ?>>
                                            🌐 Default Sistem (<?= strtoupper($globalDefaultGateway ?? 'DUITKU') ?>)
                                        </option>
                                        <option value="duitku" <?= ($application['gateway_sandbox'] ?? '') === 'duitku' ? 'selected' : '' ?>>
                                            💳 Duitku (Duitku Pop)
                                        </option>
                                        <option value="midtrans" <?= ($application['gateway_sandbox'] ?? '') === 'midtrans' ? 'selected' : '' ?>>
                                            💳 Midtrans (Snap Popup)
                                        </option>
                                        <option value="doku" <?= ($application['gateway_sandbox'] ?? '') === 'doku' ? 'selected' : '' ?>>
                                            💳 DOKU Checkout
                                        </option>
                                    </select>
                                    <small class="text-muted d-block lh-sm" style="font-size: 0.8rem;">
                                        Dipakai saat request berisi <code>mode: 'sandbox'</code> atau ketika mode tidak disertakan client.
                                    </small>
                                </div>
                            </div>

                            <!-- Gateway Mode Live -->
                            <div class="col-md-6">
                                <div class="p-3 bg-white border rounded-3 h-100 shadow-sm">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label small fw-bold mb-0 text-success">
                                            <i class="bi bi-shield-check me-1 text-success"></i> Gateway Live (Production)
                                        </label>
                                        <span class="badge bg-success-subtle text-success font-monospace small">Transaksi Riil</span>
                                    </div>
                                    <select name="gateway_live" class="form-select form-select-sm mb-2">
                                        <option value="default" <?= ($application['gateway_live'] ?? 'default') === 'default' ? 'selected' : '' ?>>
                                            🌐 Default Sistem (<?= strtoupper($globalDefaultGateway ?? 'DUITKU') ?>)
                                        </option>
                                        <option value="duitku" <?= ($application['gateway_live'] ?? '') === 'duitku' ? 'selected' : '' ?>>
                                            💳 Duitku (Duitku Pop)
                                        </option>
                                        <option value="midtrans" <?= ($application['gateway_live'] ?? '') === 'midtrans' ? 'selected' : '' ?>>
                                            💳 Midtrans (Snap Popup)
                                        </option>
                                        <option value="doku" <?= ($application['gateway_live'] ?? '') === 'doku' ? 'selected' : '' ?>>
                                            💳 DOKU Checkout
                                        </option>
                                    </select>
                                    <small class="text-muted d-block lh-sm" style="font-size: 0.8rem;">
                                        Dipakai saat client memproses transaksi riil dengan parameter <code>mode: 'live'</code>.
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Fallback / Gateway Default Platform -->
                        <div class="mt-3 pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <label class="form-label small fw-semibold mb-0 text-muted">
                                Gateway Cadangan / Fallback Platform:
                            </label>
                            <select name="default_gateway" class="form-select form-select-sm w-auto">
                                <option value="default" <?= ($application['default_gateway'] ?? 'default') === 'default' ? 'selected' : '' ?>>
                                    Default Sistem (<?= strtoupper($globalDefaultGateway ?? 'DUITKU') ?>)
                                </option>
                                <option value="duitku" <?= ($application['default_gateway'] ?? '') === 'duitku' ? 'selected' : '' ?>>Duitku</option>
                                <option value="midtrans" <?= ($application['default_gateway'] ?? '') === 'midtrans' ? 'selected' : '' ?>>Midtrans</option>
                                <option value="doku" <?= ($application['default_gateway'] ?? '') === 'doku' ? 'selected' : '' ?>>DOKU</option>
                            </select>
                        </div>
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

