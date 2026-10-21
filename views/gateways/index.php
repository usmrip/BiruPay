<?php
use App\Core\View;
?>

<!-- Global Gateway & Bulk Application Action Banner -->
<div class="card-custom mb-4 p-3 bg-white">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary text-uppercase px-2 py-1">
                    <i class="bi bi-globe me-1"></i> Default Global: <?= strtoupper($globalDefaultGateway ?? 'duitku') ?>
                </span>
                <span class="small fw-bold text-dark">Sistem Multi-Platform Biru PayHub</span>
            </div>
            <div class="small text-muted">
                Setiap platform bisa memilih gateway sendiri (Midtrans / Duitku / DOKU), atau Anda bisa menentukan gateway masal untuk semua platform sekaligus.
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= View::url('applications') ?>" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-stack me-1"></i> Tentukan Gateway Masal (<?= $totalApps ?? 0 ?> Platform)
            </a>
            <a href="<?= View::url('applications') ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-grid-3x3-gap me-1"></i> Atur Gateway Per Platform
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Midtrans Setup Card -->
    <div class="col-lg-12">
        <div class="card-custom">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded bg-primary-subtle text-primary"><i class="bi bi-credit-card"></i></span>
                    <div>
                        <h6 class="mb-0 fw-bold">1. Setup Midtrans (Snap Popup)</h6>
                        <small class="text-muted">Kelola kredensial Sandbox dan Live (Production) dalam 1 tempat</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge <?= ($midtrans['is_production'] ?? 0) ? 'bg-success' : 'bg-warning text-dark' ?>">
                        Mode Aktif: <?= ($midtrans['is_production'] ?? 0) ? 'Live (Production)' : 'Sandbox (Testing)' ?>
                    </span>
                    <span class="badge <?= ($midtrans['is_active'] ?? 0) ? 'bg-primary' : 'bg-secondary' ?>">
                        <?= ($midtrans['is_active'] ?? 0) ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <form action="<?= View::url('gateways/midtrans') ?>" method="POST">
                    <?= View::csrfField() ?>

                    <!-- Mode & Status Selector -->
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <div class="row align-items-center g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">Status Gateway:</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="midtrans_active" value="1" <?= ($midtrans['is_active'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="midtrans_active">Aktifkan Gateway Midtrans di Checkout</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">Pilih Mode Environment Aktif:</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="mode" id="midtrans_mode_sandbox" value="sandbox" <?= !($midtrans['is_production'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold text-warning-emphasis" for="midtrans_mode_sandbox">
                                            <i class="bi bi-cone-striped"></i> Sandbox (Testing)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="mode" id="midtrans_mode_prod" value="production" <?= ($midtrans['is_production'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold text-success" for="midtrans_mode_prod">
                                            <i class="bi bi-shield-check"></i> Live (Production)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dual Environment Panels -->
                    <div class="row g-4 mb-4">
                        <!-- Sandbox Panel -->
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border border-warning bg-warning bg-opacity-10 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-warning-emphasis mb-0">
                                        <i class="bi bi-cone-striped me-1"></i> Kredensial SANDBOX (Testing)
                                    </h6>
                                    <span class="badge bg-warning text-dark">Uji Coba</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Sandbox Client Key</label>
                                    <input type="text" name="sandbox_client_key" class="form-control form-control-sm bg-white font-monospace" value="<?= View::escape($midtrans['sandbox_client_key'] ?? $midtrans['client_key'] ?? '') ?>" placeholder="SB-Mid-client-...">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Sandbox Server Key</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="sandbox_server_key" class="form-control bg-white font-monospace" value="<?= View::escape($midtrans['sandbox_server_key'] ?? $midtrans['server_key'] ?? '') ?>" placeholder="SB-Mid-server-...">
                                        <button class="btn btn-outline-secondary toggle-password" type="button"><i class="bi bi-eye"></i></button>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Sandbox Merchant ID (Opsional)</label>
                                    <input type="text" name="sandbox_merchant_id" class="form-control form-control-sm bg-white font-monospace" value="<?= View::escape($midtrans['sandbox_merchant_id'] ?? $midtrans['merchant_id'] ?? '') ?>" placeholder="G123456789">
                                </div>
                            </div>
                        </div>

                        <!-- Production Panel -->
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border border-success bg-success bg-opacity-10 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-success mb-0">
                                        <i class="bi bi-shield-check me-1"></i> Kredensial LIVE (Production)
                                    </h6>
                                    <span class="badge bg-success">Transaksi Asli</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Production Client Key</label>
                                    <input type="text" name="production_client_key" class="form-control form-control-sm bg-white font-monospace" value="<?= View::escape($midtrans['production_client_key'] ?? '') ?>" placeholder="Mid-client-...">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Production Server Key</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="production_server_key" class="form-control bg-white font-monospace" value="<?= View::escape($midtrans['production_server_key'] ?? '') ?>" placeholder="Mid-server-...">
                                        <button class="btn btn-outline-secondary toggle-password" type="button"><i class="bi bi-eye"></i></button>
                                    </div>
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Production Merchant ID (Opsional)</label>
                                    <input type="text" name="production_merchant_id" class="form-control form-control-sm bg-white font-monospace" value="<?= View::escape($midtrans['production_merchant_id'] ?? '') ?>" placeholder="M123456789">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Webhook instructions -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">
                            <i class="bi bi-link-45deg text-primary"></i> Midtrans Notification / Webhook URL:
                        </label>
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control font-monospace bg-white" readonly value="<?= View::url('webhook/midtrans') ?>">
                            <button class="btn btn-outline-secondary btn-copy-clipboard" type="button" data-clipboard-text="<?= View::url('webhook/midtrans') ?>">
                                <i class="bi bi-clipboard"></i> Salin URL
                            </button>
                        </div>
                        <small class="text-muted d-block mt-1">
                            Pasang URL di atas pada menu <strong>Settings &rarr; Configuration &rarr; Payment Notification URL</strong> di dashboard Sandbox maupun Production Midtrans.
                        </small>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Konfigurasi Midtrans
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Duitku Setup Card -->
    <div class="col-lg-12">
        <div class="card-custom">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded bg-warning-subtle text-warning"><i class="bi bi-cash-stack"></i></span>
                    <div>
                        <h6 class="mb-0 fw-bold">2. Setup Duitku (Pop Modal)</h6>
                        <small class="text-muted">Kelola kredensial Sandbox dan Live (Production) Duitku Pop (https://docs.duitku.com/pop/id)</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge <?= ($duitku['is_production'] ?? 0) ? 'bg-success' : 'bg-warning text-dark' ?>">
                        Mode Aktif: <?= ($duitku['is_production'] ?? 0) ? 'Live (Production)' : 'Sandbox (Testing)' ?>
                    </span>
                    <span class="badge <?= ($duitku['is_active'] ?? 0) ? 'bg-primary' : 'bg-secondary' ?>">
                        <?= ($duitku['is_active'] ?? 0) ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <form action="<?= View::url('gateways/duitku') ?>" method="POST">
                    <?= View::csrfField() ?>

                    <!-- Mode & Status Selector -->
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <div class="row align-items-center g-3">
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark mb-1">Status Gateway:</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="duitku_active" value="1" <?= ($duitku['is_active'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="duitku_active">Aktifkan Duitku di Checkout</label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small fw-bold text-dark mb-1">Pilih Mode Environment Aktif:</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="mode" id="duitku_mode_sandbox" value="sandbox" <?= !($duitku['is_production'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold text-warning-emphasis" for="duitku_mode_sandbox">
                                            <i class="bi bi-cone-striped"></i> Sandbox (Testing)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="mode" id="duitku_mode_prod" value="production" <?= ($duitku['is_production'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold text-success" for="duitku_mode_prod">
                                            <i class="bi bi-shield-check"></i> Live (Production)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Kadaluarsa Invoice (Menit)</label>
                                <input type="number" name="expiry_period" class="form-control form-control-sm" value="<?= View::escape($duitku['additional']['expiry_period'] ?? 1440) ?>" min="5" max="43200">
                            </div>
                        </div>
                    </div>

                    <!-- Dual Environment Panels -->
                    <div class="row g-4 mb-4">
                        <!-- Sandbox Panel -->
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border border-warning bg-warning bg-opacity-10 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-warning-emphasis mb-0">
                                        <i class="bi bi-cone-striped me-1"></i> Kredensial SANDBOX (Testing)
                                    </h6>
                                    <span class="badge bg-warning text-dark">Uji Coba</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Sandbox Merchant Code</label>
                                    <input type="text" name="sandbox_merchant_id" class="form-control form-control-sm bg-white font-monospace" value="<?= View::escape($duitku['sandbox_merchant_id'] ?? $duitku['merchant_id'] ?? '') ?>" placeholder="D1234">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Sandbox API Key Duitku</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="sandbox_server_key" class="form-control bg-white font-monospace" value="<?= View::escape($duitku['sandbox_server_key'] ?? $duitku['server_key'] ?? '') ?>" placeholder="API Key Sandbox">
                                        <button class="btn btn-outline-secondary toggle-password" type="button"><i class="bi bi-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Production Panel -->
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border border-success bg-success bg-opacity-10 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-success mb-0">
                                        <i class="bi bi-shield-check me-1"></i> Kredensial LIVE (Production)
                                    </h6>
                                    <span class="badge bg-success">Transaksi Asli</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Production Merchant Code</label>
                                    <input type="text" name="production_merchant_id" class="form-control form-control-sm bg-white font-monospace" value="<?= View::escape($duitku['production_merchant_id'] ?? '') ?>" placeholder="M1234">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Production API Key Duitku</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="production_server_key" class="form-control bg-white font-monospace" value="<?= View::escape($duitku['production_server_key'] ?? '') ?>" placeholder="API Key Production">
                                        <button class="btn btn-outline-secondary toggle-password" type="button"><i class="bi bi-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Webhook instructions -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">
                            <i class="bi bi-link-45deg text-warning"></i> Duitku Callback / Webhook URL:
                        </label>
                        <div class="input-group input-group-sm mb-2">
                            <input type="text" class="form-control font-monospace bg-white" readonly value="<?= View::url('webhook/duitku') ?>">
                            <button class="btn btn-outline-secondary btn-copy-clipboard" type="button" data-clipboard-text="<?= View::url('webhook/duitku') ?>">
                                <i class="bi bi-clipboard"></i> Salin URL
                            </button>
                        </div>
                        <small class="text-muted d-block">
                            Pasang URL di atas pada menu <strong>Proyek &rarr; Konfigurasi &rarr; Callback URL</strong> di dashboard merchant Duitku.
                        </small>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Konfigurasi Duitku
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- DOKU Setup Card -->
    <div class="col-lg-12">
        <div class="card-custom">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded bg-danger-subtle text-danger"><i class="bi bi-shield-lock"></i></span>
                    <div>
                        <h6 class="mb-0 fw-bold">3. Setup DOKU (Doku.com)</h6>
                        <small class="text-muted">Kelola kredensial Sandbox dan Live (Production) DOKU Checkout</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge <?= ($doku['is_production'] ?? 0) ? 'bg-success' : 'bg-warning text-dark' ?>">
                        Mode Aktif: <?= ($doku['is_production'] ?? 0) ? 'Live (Production)' : 'Sandbox (Testing)' ?>
                    </span>
                    <span class="badge <?= ($doku['is_active'] ?? 0) ? 'bg-primary' : 'bg-secondary' ?>">
                        <?= ($doku['is_active'] ?? 0) ? 'Aktif' : 'Nonaktif' ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <form action="<?= View::url('gateways/doku') ?>" method="POST">
                    <?= View::csrfField() ?>

                    <!-- Mode & Status Selector -->
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <div class="row align-items-center g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">Status Gateway:</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="doku_active" value="1" <?= ($doku['is_active'] ?? 0) ? 'checked' : '' ?>>
                                    <label class="form-check-label fw-semibold" for="doku_active">Aktifkan DOKU di Checkout</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-dark mb-1">Pilih Mode Environment Aktif:</label>
                                <div class="d-flex gap-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="mode" id="doku_mode_sandbox" value="sandbox" <?= !($doku['is_production'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold text-warning-emphasis" for="doku_mode_sandbox">
                                            <i class="bi bi-cone-striped"></i> Sandbox (Testing)
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="mode" id="doku_mode_prod" value="production" <?= ($doku['is_production'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label fw-bold text-success" for="doku_mode_prod">
                                            <i class="bi bi-shield-check"></i> Live (Production)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dual Environment Panels -->
                    <div class="row g-4 mb-4">
                        <!-- Sandbox Panel -->
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border border-warning bg-warning bg-opacity-10 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-warning-emphasis mb-0">
                                        <i class="bi bi-cone-striped me-1"></i> Kredensial SANDBOX (Testing)
                                    </h6>
                                    <span class="badge bg-warning text-dark">Uji Coba</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Sandbox Client ID</label>
                                    <input type="text" name="sandbox_client_key" class="form-control form-control-sm bg-white font-monospace" value="<?= View::escape($doku['sandbox_client_key'] ?? $doku['client_key'] ?? '') ?>" placeholder="MCH-SANDBOX-...">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Sandbox Secret Key</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="sandbox_server_key" class="form-control bg-white font-monospace" value="<?= View::escape($doku['sandbox_server_key'] ?? $doku['server_key'] ?? '') ?>" placeholder="SK-SANDBOX-...">
                                        <button class="btn btn-outline-secondary toggle-password" type="button"><i class="bi bi-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Production Panel -->
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 border border-success bg-success bg-opacity-10 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-success mb-0">
                                        <i class="bi bi-shield-check me-1"></i> Kredensial LIVE (Production)
                                    </h6>
                                    <span class="badge bg-success">Transaksi Asli</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Production Client ID</label>
                                    <input type="text" name="production_client_key" class="form-control form-control-sm bg-white font-monospace" value="<?= View::escape($doku['production_client_key'] ?? '') ?>" placeholder="MCH-LIVE-...">
                                </div>

                                <div class="mb-2">
                                    <label class="form-label small fw-semibold">Production Secret Key</label>
                                    <div class="input-group input-group-sm">
                                        <input type="password" name="production_server_key" class="form-control bg-white font-monospace" value="<?= View::escape($doku['production_server_key'] ?? '') ?>" placeholder="SK-LIVE-...">
                                        <button class="btn btn-outline-secondary toggle-password" type="button"><i class="bi bi-eye"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Webhook instructions -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">
                            <i class="bi bi-link-45deg text-danger"></i> DOKU Notification / Webhook URL:
                        </label>
                        <div class="input-group input-group-sm mb-2">
                            <input type="text" class="form-control font-monospace bg-white" readonly value="<?= View::url('webhook/doku') ?>">
                            <button class="btn btn-outline-secondary btn-copy-clipboard" type="button" data-clipboard-text="<?= View::url('webhook/doku') ?>">
                                <i class="bi bi-clipboard"></i> Salin URL
                            </button>
                        </div>
                        <small class="text-muted d-block">
                            Daftarkan URL di atas pada portal DOKU Back Office &rarr; Configuration &rarr; URL Notification.
                        </small>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Konfigurasi DOKU
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
