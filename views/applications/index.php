<?php
use App\Core\View;
?>

<!-- Action Bar & Gateway Controls -->
<div class="row align-items-center mb-4 g-3">
    <div class="col-md-4">
        <h5 class="fw-bold mb-0">Platform & Aplikasi Biru</h5>
        <small class="text-muted">Kelola platform Biru, mode (Live/Sandbox), serta penentuan gateway pembayaran</small>
    </div>
    <div class="col-md-8 d-flex flex-wrap justify-content-md-end gap-2">
        <!-- Global Gateway Indicator & Change Modal -->
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalGlobalGateway">
            <i class="bi bi-globe me-1"></i> Default Sistem: 
            <span class="badge bg-primary text-uppercase"><?= View::escape($globalDefaultGateway ?? 'duitku') ?></span>
        </button>

        <!-- Bulk Mode Assignment Button -->
        <button type="button" class="btn btn-outline-warning text-dark" data-bs-toggle="modal" data-bs-target="#modalBulkMode">
            <i class="bi bi-toggle2-on me-1"></i> Mode Masal
        </button>

        <!-- Bulk Gateway Assignment Button -->
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalBulkGateway">
            <i class="bi bi-stack me-1"></i> Gateway Masal
        </button>

        <!-- Create Application Button -->
        <a href="<?= View::url('applications/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Platform
        </a>
    </div>
</div>

<div class="card-custom mb-4">
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Kode & Nama Platform</th>
                    <th>Mode Platform</th>
                    <th>Payment Gateway</th>
                    <th>API Key (Secret)</th>
                    <th>Webhook Callback URL</th>
                    <th>Total Trx</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($applications)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-grid-3x3-gap display-6 d-block mb-2 text-secondary opacity-50"></i>
                            Belum ada platform atau aplikasi yang didaftarkan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($applications as $app): 
                        $mode = $app['resolved_mode'] ?? 'sandbox';
                        $isLive = ($mode === 'live');
                        $effGw = $app['effective_gateway'] ?? 'duitku';
                        $gwLive = $app['gateway_live'] ?? 'default';
                        $gwSand = $app['gateway_sandbox'] ?? 'default';
                    ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= View::escape($app['app_name']) ?></div>
                                <span class="badge bg-secondary-subtle text-secondary font-monospace"><?= View::escape($app['app_code']) ?></span>
                                <span class="badge bg-light text-dark border ms-1"><i class="bi bi-clock me-1"></i><?= View::escape($app['timezone'] ?? 'UTC') ?></span>
                            </td>
                            <td>
                                <!-- Quick Mode Dropdown / Toggle -->
                                <div class="dropdown">
                                    <button class="btn btn-sm dropdown-toggle d-flex align-items-center gap-1 <?= $isLive ? 'btn-success' : 'btn-warning text-dark' ?>" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Klik untuk ubah Mode">
                                        <?php if ($isLive): ?>
                                            <i class="bi bi-broadcast"></i> <span class="fw-bold">LIVE</span>
                                        <?php else: ?>
                                            <i class="bi bi-cone-striped"></i> <span class="fw-bold">SANDBOX</span>
                                        <?php endif; ?>
                                    </button>
                                    <ul class="dropdown-menu shadow-sm">
                                        <li><h6 class="dropdown-header small">Pilih Mode Platform</h6></li>
                                        <li>
                                            <form action="<?= View::url('applications/' . $app['id'] . '/quick-mode') ?>" method="POST">
                                                <?= View::csrfField() ?>
                                                <input type="hidden" name="mode" value="sandbox">
                                                <button type="submit" class="dropdown-item small d-flex align-items-center gap-2 <?= !$isLive ? 'active' : '' ?>">
                                                    <span class="badge bg-warning text-dark"><i class="bi bi-cone-striped me-1"></i>SANDBOX</span>
                                                    <span>Uji Coba (Default)</span>
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="<?= View::url('applications/' . $app['id'] . '/quick-mode') ?>" method="POST">
                                                <?= View::csrfField() ?>
                                                <input type="hidden" name="mode" value="live">
                                                <button type="submit" class="dropdown-item small d-flex align-items-center gap-2 <?= $isLive ? 'active' : '' ?>">
                                                    <span class="badge bg-success text-white"><i class="bi bi-broadcast me-1"></i>LIVE</span>
                                                    <span>Produksi Riil</span>
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <?php if ($effGw === 'duitku'): ?>
                                            <span class="badge bg-info text-dark"><i class="bi bi-wallet2 me-1"></i> Duitku</span>
                                        <?php elseif ($effGw === 'midtrans'): ?>
                                            <span class="badge bg-primary"><i class="bi bi-credit-card me-1"></i> Midtrans</span>
                                        <?php elseif ($effGw === 'doku'): ?>
                                            <span class="badge bg-danger"><i class="bi bi-shield-check me-1"></i> DOKU</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Default (<?= strtoupper($globalDefaultGateway ?? 'duitku') ?>)</span>
                                        <?php endif; ?>
                                    </button>
                                    <ul class="dropdown-menu shadow-sm" style="min-width: 260px;">
                                        <li><h6 class="dropdown-header small">Gateway Khusus Mode LIVE:</h6></li>
                                        <li>
                                            <form action="<?= View::url('applications/' . $app['id'] . '/quick-gateway') ?>" method="POST">
                                                <?= View::csrfField() ?>
                                                <input type="hidden" name="target" value="live">
                                                <input type="hidden" name="gateway" value="default">
                                                <button type="submit" class="dropdown-item small <?= $gwLive === 'default' ? 'active' : '' ?>">
                                                    <i class="bi bi-globe me-2"></i> Live: Default Sistem (<?= strtoupper($globalDefaultGateway ?? 'duitku') ?>)
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="<?= View::url('applications/' . $app['id'] . '/quick-gateway') ?>" method="POST">
                                                <?= View::csrfField() ?>
                                                <input type="hidden" name="target" value="live">
                                                <input type="hidden" name="gateway" value="duitku">
                                                <button type="submit" class="dropdown-item small <?= $gwLive === 'duitku' ? 'active' : '' ?>">
                                                    <i class="bi bi-wallet2 me-2 text-info"></i> Live: Duitku (Duitku Pop)
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="<?= View::url('applications/' . $app['id'] . '/quick-gateway') ?>" method="POST">
                                                <?= View::csrfField() ?>
                                                <input type="hidden" name="target" value="live">
                                                <input type="hidden" name="gateway" value="midtrans">
                                                <button type="submit" class="dropdown-item small <?= $gwLive === 'midtrans' ? 'active' : '' ?>">
                                                    <i class="bi bi-credit-card me-2 text-primary"></i> Live: Midtrans (Snap Popup)
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="<?= View::url('applications/' . $app['id'] . '/quick-gateway') ?>" method="POST">
                                                <?= View::csrfField() ?>
                                                <input type="hidden" name="target" value="live">
                                                <input type="hidden" name="gateway" value="doku">
                                                <button type="submit" class="dropdown-item small <?= $gwLive === 'doku' ? 'active' : '' ?>">
                                                    <i class="bi bi-shield-check me-2 text-danger"></i> Live: DOKU Checkout
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><h6 class="dropdown-header small">Gateway Khusus Mode SANDBOX:</h6></li>
                                        <li>
                                            <form action="<?= View::url('applications/' . $app['id'] . '/quick-gateway') ?>" method="POST">
                                                <?= View::csrfField() ?>
                                                <input type="hidden" name="target" value="sandbox">
                                                <input type="hidden" name="gateway" value="default">
                                                <button type="submit" class="dropdown-item small <?= $gwSand === 'default' ? 'active' : '' ?>">
                                                    <i class="bi bi-globe me-2"></i> Sandbox: Default Sistem (<?= strtoupper($globalDefaultGateway ?? 'duitku') ?>)
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="<?= View::url('applications/' . $app['id'] . '/quick-gateway') ?>" method="POST">
                                                <?= View::csrfField() ?>
                                                <input type="hidden" name="target" value="sandbox">
                                                <input type="hidden" name="gateway" value="duitku">
                                                <button type="submit" class="dropdown-item small <?= $gwSand === 'duitku' ? 'active' : '' ?>">
                                                    <i class="bi bi-wallet2 me-2 text-info"></i> Sandbox: Duitku (Duitku Pop)
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="<?= View::url('applications/' . $app['id'] . '/quick-gateway') ?>" method="POST">
                                                <?= View::csrfField() ?>
                                                <input type="hidden" name="target" value="sandbox">
                                                <input type="hidden" name="gateway" value="midtrans">
                                                <button type="submit" class="dropdown-item small <?= $gwSand === 'midtrans' ? 'active' : '' ?>">
                                                    <i class="bi bi-credit-card me-2 text-primary"></i> Sandbox: Midtrans (Snap Popup)
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <form action="<?= View::url('applications/' . $app['id'] . '/quick-gateway') ?>" method="POST">
                                                <?= View::csrfField() ?>
                                                <input type="hidden" name="target" value="sandbox">
                                                <input type="hidden" name="gateway" value="doku">
                                                <button type="submit" class="dropdown-item small <?= $gwSand === 'doku' ? 'active' : '' ?>">
                                                    <i class="bi bi-shield-check me-2 text-danger"></i> Sandbox: DOKU Checkout
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                                <div class="text-muted mt-1" style="font-size: 11px;">
                                    <span class="text-success"><i class="bi bi-broadcast"></i> <?= strtoupper($app['live_gateway'] ?? 'default') ?></span>
                                    <span class="mx-1 text-secondary">|</span>
                                    <span class="text-warning-emphasis"><i class="bi bi-cone-striped"></i> <?= strtoupper($app['sandbox_gateway'] ?? 'default') ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="input-group input-group-sm" style="max-width: 210px;">
                                    <input type="password" class="form-control font-monospace" value="<?= View::escape($app['api_key']) ?>" readonly>
                                    <button class="btn btn-outline-secondary toggle-password" type="button" title="Lihat Key"><i class="bi bi-eye"></i></button>
                                    <button class="btn btn-outline-secondary btn-copy-clipboard" type="button" data-clipboard-text="<?= View::escape($app['api_key']) ?>" title="Salin Key"><i class="bi bi-clipboard"></i></button>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($app['webhook_url'])): ?>
                                    <span class="small font-monospace text-truncate d-inline-block" style="max-width: 180px;" title="<?= View::escape($app['webhook_url']) ?>">
                                        <?= View::escape($app['webhook_url']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small"><em>Belum diatur</em></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= number_format($app['trx_count'] ?? 0) ?></span>
                            </td>
                            <td>
                                <span class="badge <?= $app['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($app['status']) ?>
                                </span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= View::url('applications/' . $app['id'] . '/edit') ?>" class="btn btn-light border" title="Edit Platform">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="<?= View::url('applications/' . $app['id'] . '/regenerate-key') ?>" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membuat ulang API Key untuk platform ini?');">
                                        <?= View::csrfField() ?>
                                        <button type="submit" class="btn btn-light border text-warning" title="Buat Ulang API Key">
                                            <i class="bi bi-key"></i>
                                        </button>
                                    </form>
                                    <form action="<?= View::url('applications/' . $app['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Hapus platform ini?');">
                                        <?= View::csrfField() ?>
                                        <button type="submit" class="btn btn-light border text-danger" title="Hapus Platform">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: PENENTUAN GATEWAY MASAL (BULK GATEWAY ASSIGNMENT)       -->
<!-- ============================================================== -->
<div class="modal fade" id="modalBulkGateway" tabindex="-1" aria-labelledby="modalBulkGatewayLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="modalBulkGatewayLabel">
                    <i class="bi bi-stack me-2"></i> Tentukan Gateway Masal untuk Semua Platform
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= View::url('applications/bulk-gateway') ?>" method="POST">
                <?= View::csrfField() ?>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Gunakan fitur ini untuk <strong>mengubah payment gateway seluruh platform atau aplikasi terdaftar sekaligus</strong> dengan 1 klik.
                    </p>

                    <label class="form-label small fw-bold">Pilih Gateway untuk Diterapkan ke SEMUA Platform:</label>
                    <div class="d-flex flex-column gap-2 mb-3">
                        <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer bg-light">
                            <input class="form-check-input mt-0" type="radio" name="target_gateway" value="default" checked>
                            <div>
                                <div class="fw-bold text-dark">Gunakan Default Sistem</div>
                                <small class="text-muted">Semua platform akan mengikuti gateway default global sistem (saat ini: <strong><?= strtoupper($globalDefaultGateway ?? 'duitku') ?></strong>)</small>
                            </div>
                        </label>

                        <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer bg-light">
                            <input class="form-check-input mt-0" type="radio" name="target_gateway" value="duitku">
                            <div>
                                <div class="fw-bold text-dark"><i class="bi bi-wallet2 text-info me-1"></i> Duitku (Duitku Pop Modal)</div>
                                <small class="text-muted">Ubah semua platform agar memakai Duitku sebagai gateway utama</small>
                            </div>
                        </label>

                        <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer bg-light">
                            <input class="form-check-input mt-0" type="radio" name="target_gateway" value="midtrans">
                            <div>
                                <div class="fw-bold text-dark"><i class="bi bi-credit-card text-primary me-1"></i> Midtrans (Snap Popup)</div>
                                <small class="text-muted">Ubah semua platform agar memakai Midtrans sebagai gateway utama</small>
                            </div>
                        </label>

                        <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer bg-light">
                            <input class="form-check-input mt-0" type="radio" name="target_gateway" value="doku">
                            <div>
                                <div class="fw-bold text-dark"><i class="bi bi-shield-check text-danger me-1"></i> DOKU Checkout</div>
                                <small class="text-muted">Ubah semua platform agar memakai DOKU sebagai gateway utama</small>
                            </div>
                        </label>
                    </div>

                    <div class="alert alert-warning py-2 small mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i> Perubahan ini akan langsung berdampak pada seluruh pembuatan tagihan baru di semua platform Biru.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-bold">
                        <i class="bi bi-check-lg me-1"></i> Terapkan ke Semua Platform
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: PENENTUAN MODE MASAL (BULK MODE ASSIGNMENT)             -->
<!-- ============================================================== -->
<div class="modal fade" id="modalBulkMode" tabindex="-1" aria-labelledby="modalBulkModeLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold" id="modalBulkModeLabel">
                    <i class="bi bi-toggle2-on me-2"></i> Tentukan Mode Masal untuk Semua Platform
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= View::url('applications/bulk-mode') ?>" method="POST">
                <?= View::csrfField() ?>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Gunakan fitur ini untuk <strong>mengubah mode (Sandbox vs Live) seluruh platform terdaftar sekaligus</strong> dengan 1 klik.
                    </p>

                    <label class="form-label small fw-bold">Pilih Mode untuk Diterapkan ke SEMUA Platform:</label>
                    <div class="d-flex flex-column gap-2 mb-3">
                        <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer bg-light">
                            <input class="form-check-input mt-0" type="radio" name="target_mode" value="sandbox" checked>
                            <div>
                                <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                    <span class="badge bg-warning text-dark"><i class="bi bi-cone-striped me-1"></i>SANDBOX</span>
                                    <span>(Uji Coba / Testing)</span>
                                </div>
                                <small class="text-muted">Semua platform akan beralih ke mode simulasi transaksi (tanpa uang nyata)</small>
                            </div>
                        </label>

                        <label class="p-3 border rounded-3 d-flex align-items-center gap-3 cursor-pointer bg-light">
                            <input class="form-check-input mt-0" type="radio" name="target_mode" value="live">
                            <div>
                                <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                    <span class="badge bg-success text-white"><i class="bi bi-broadcast me-1"></i>LIVE</span>
                                    <span>(Produksi Riil)</span>
                                </div>
                                <small class="text-muted">Semua platform akan beralih ke mode pembayaran operasional riil dengan uang sungguhan</small>
                            </div>
                        </label>
                    </div>

                    <div class="alert alert-warning py-2 small mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i> Mode Live akan menggunakan kredensial produksi dan payment gateway live yang dipilih di masing-masing platform.
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning btn-sm px-3 fw-bold text-dark">
                        <i class="bi bi-check-lg me-1"></i> Terapkan Mode ke Semua Platform
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL: SET DEFAULT GLOBAL GATEWAY SISTEM                       -->
<!-- ============================================================== -->
<div class="modal fade" id="modalGlobalGateway" tabindex="-1" aria-labelledby="modalGlobalGatewayLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalGlobalGatewayLabel">
                    <i class="bi bi-globe me-2"></i> Set Default Payment Gateway Sistem
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= View::url('applications/global-default') ?>" method="POST">
                <?= View::csrfField() ?>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        Gateway default sistem adalah gateway cadangan utama yang digunakan oleh platform apapun yang disetel <em>"Gunakan Default Sistem"</em>.
                    </p>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Gateway Default Global:</label>
                        <select name="global_gateway" class="form-select">
                            <option value="duitku" <?= ($globalDefaultGateway ?? '') === 'duitku' ? 'selected' : '' ?>>
                                Duitku (Duitku Pop Modal)
                            </option>
                            <option value="midtrans" <?= ($globalDefaultGateway ?? '') === 'midtrans' ? 'selected' : '' ?>>
                                Midtrans (Snap Popup)
                            </option>
                            <option value="doku" <?= ($globalDefaultGateway ?? '') === 'doku' ? 'selected' : '' ?>>
                                DOKU Checkout
                            </option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-dark btn-sm px-3 fw-bold">
                        <i class="bi bi-save me-1"></i> Simpan Gateway Default
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
