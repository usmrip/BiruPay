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

                    <!-- Konfigurasi Mode & Payment Gateway Platform -->
                    <div class="card mb-4 border-0 shadow-sm" style="background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%); border: 1px solid #e2e8f0 !important; border-radius: 14px;">
                        <div class="card-body p-3 p-md-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-toggles2 text-primary me-2"></i>Mode & Gateway Platform</h6>
                                    <small class="text-muted">Pilih mode operasi platform ini dan gateway yang digunakan untuk masing-masing mode.</small>
                                </div>
                            </div>

                            <!-- Mode Selector -->
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-uppercase text-secondary" style="letter-spacing: 0.5px;">Mode Transaksi Platform</label>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="d-block p-3 rounded-3 border cursor-pointer position-relative <?= ($application['mode'] ?? 'sandbox') === 'sandbox' ? 'border-primary bg-primary-subtle bg-opacity-10' : 'bg-white' ?>" for="mode_sandbox" style="transition: all .15s ease;">
                                            <div class="d-flex align-items-start gap-2">
                                                <input class="form-check-input mt-1" type="radio" name="mode" id="mode_sandbox" value="sandbox" <?= ($application['mode'] ?? 'sandbox') === 'sandbox' ? 'checked' : '' ?>>
                                                <div>
                                                    <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                                        <span class="badge bg-warning text-dark"><i class="bi bi-cone-striped me-1"></i>SANDBOX</span>
                                                        <span>(Uji Coba)</span>
                                                    </div>
                                                    <div class="small text-muted mt-1" style="font-size: 12px; line-height: 1.4;">
                                                        Simulasi transaksi tanpa uang nyata. <strong>Default otomatis</strong> jika client/caller tidak menyebutkan parameter mode.
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="d-block p-3 rounded-3 border cursor-pointer position-relative <?= ($application['mode'] ?? '') === 'live' ? 'border-success bg-success-subtle bg-opacity-10' : 'bg-white' ?>" for="mode_live" style="transition: all .15s ease;">
                                            <div class="d-flex align-items-start gap-2">
                                                <input class="form-check-input mt-1" type="radio" name="mode" id="mode_live" value="live" <?= ($application['mode'] ?? '') === 'live' ? 'checked' : '' ?>>
                                                <div>
                                                    <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                                        <span class="badge bg-success text-white"><i class="bi bi-broadcast me-1"></i>LIVE</span>
                                                        <span>(Produksi Riil)</span>
                                                    </div>
                                                    <div class="small text-muted mt-1" style="font-size: 12px; line-height: 1.4;">
                                                        Operasional nyata dengan uang riil pelanggan menggunakan kredensial gateway produksi.
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Per-Mode Gateway Selection -->
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-dark">
                                        <i class="bi bi-broadcast text-success me-1"></i> Gateway saat Mode LIVE
                                    </label>
                                    <select name="gateway_live" class="form-select">
                                        <option value="default" <?= ($application['gateway_live'] ?? 'default') === 'default' ? 'selected' : '' ?>>
                                            🌐 Ikuti Default Sistem (<?= strtoupper($globalDefaultGateway ?? 'DUITKU') ?>)
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
                                    <small class="text-muted" style="font-size: 11.5px;">Gateway yang otomatis aktif jika transaksi dalam mode Live.</small>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-dark">
                                        <i class="bi bi-cone-striped text-warning me-1"></i> Gateway saat Mode SANDBOX
                                    </label>
                                    <select name="gateway_sandbox" class="form-select">
                                        <option value="default" <?= ($application['gateway_sandbox'] ?? 'default') === 'default' ? 'selected' : '' ?>>
                                            🌐 Ikuti Default Sistem (<?= strtoupper($globalDefaultGateway ?? 'DUITKU') ?>)
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
                                    <small class="text-muted" style="font-size: 11.5px;">Gateway yang otomatis aktif jika transaksi dalam mode Sandbox.</small>
                                </div>
                            </div>

                            <!-- Fallback Gateway -->
                            <div class="mt-3 pt-3 border-top">
                                <div class="row align-items-center g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-muted mb-0">Gateway Cadangan / Default Platform</label>
                                        <div class="small text-muted" style="font-size: 11px;">Digunakan jika opsi per-mode diset "Ikuti Default Sistem"</div>
                                    </div>
                                    <div class="col-md-6">
                                        <select name="default_gateway" class="form-select form-select-sm">
                                            <option value="default" <?= ($application['default_gateway'] ?? 'default') === 'default' ? 'selected' : '' ?>>
                                                🌐 Default Sistem (<?= strtoupper($globalDefaultGateway ?? 'DUITKU') ?>)
                                            </option>
                                            <option value="duitku" <?= ($application['default_gateway'] ?? '') === 'duitku' ? 'selected' : '' ?>>Duitku</option>
                                            <option value="midtrans" <?= ($application['default_gateway'] ?? '') === 'midtrans' ? 'selected' : '' ?>>Midtrans</option>
                                            <option value="doku" <?= ($application['default_gateway'] ?? '') === 'doku' ? 'selected' : '' ?>>DOKU</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
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

