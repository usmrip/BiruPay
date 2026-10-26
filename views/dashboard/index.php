<?php
use App\Core\View;
use App\Core\Session;
$jam = (int) date('G');
$salam = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 19 ? 'Selamat sore' : 'Selamat malam'));
?>

<!-- Apple-style Greeting Header -->
<div class="page-greeting mb-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div>
        <h4><?= $salam ?>, <?= View::escape(Session::get('user_name', 'Admin')) ?></h4>
        <p>Ringkasan performa pembayaran real-time dari seluruh platform Biru.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= View::url('transactions') ?>" class="btn btn-primary btn-pill px-3 shadow-none">
            <i class="bi bi-receipt me-1"></i> Laporan Transaksi
        </a>
    </div>
</div>

<!-- Apple KPI Stat Widgets -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="bi bi-wallet2"></i>
            </div>
            <div class="flex-grow-1">
                <div class="stat-label">Total Volume Transaksi</div>
                <div class="stat-value"><?= View::formatRupiah($stats['total_volume'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div class="flex-grow-1">
                <div class="stat-label">Transaksi Berhasil</div>
                <div class="stat-value"><?= number_format($stats['total_success'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="flex-grow-1">
                <div class="stat-label">Menunggu Pembayaran</div>
                <div class="stat-value"><?= number_format($stats['total_pending'] ?? 0) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="bi bi-calendar2-check"></i>
            </div>
            <div class="flex-grow-1">
                <div class="stat-label">Pemasukan Hari Ini (<?= $stats['today_success'] ?? 0 ?> trx)</div>
                <div class="stat-value"><?= View::formatRupiah($stats['today_volume'] ?? 0) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- Gateway Status Widgets -->
<div class="row g-3 mb-4">
    <?php foreach ($gateways as $gw): 
        $isProd = !empty($gw['is_production']);
    ?>
        <div class="col-md-4">
            <div class="gw-widget">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-icon primary" style="width:36px;height:36px;font-size:1rem;border-radius:10px;">
                            <i class="bi bi-credit-card-2-front"></i>
                        </span>
                        <div>
                            <h6 class="mb-0 fw-bold"><?= View::escape($gw['gateway_name']) ?></h6>
                            <span class="text-muted font-monospace" style="font-size: 11px;"><?= strtoupper($gw['gateway_code']) ?></span>
                        </div>
                    </div>
                    <?php if ($gw['is_active']): ?>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                    <?php else: ?>
                        <span class="badge bg-secondary-subtle text-secondary border">Nonaktif</span>
                    <?php endif; ?>
                </div>

                <div class="gw-row">
                    <span>Mode Gateway</span>
                    <?php if ($isProd): ?>
                        <span class="badge bg-success text-white"><i class="bi bi-broadcast me-1"></i>LIVE (Produksi)</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark"><i class="bi bi-cone-striped me-1"></i>SANDBOX (Testing)</span>
                    <?php endif; ?>
                </div>

                <div class="gw-row">
                    <span>Tipe Integrasi</span>
                    <strong>
                        <?php if ($gw['gateway_code'] === 'midtrans'): ?>
                            Snap Popup
                        <?php elseif ($gw['gateway_code'] === 'duitku'): ?>
                            Duitku Pop Modal
                        <?php else: ?>
                            DOKU Checkout
                        <?php endif; ?>
                    </strong>
                </div>

                <a href="<?= View::url('gateways') ?>" class="btn btn-sm btn-light border w-100 btn-pill">
                    <i class="bi bi-sliders me-1"></i> Konfigurasi Gateway
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Recent Transactions & Applications breakdown -->
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card-custom mb-0 h-100">
            <div class="card-header justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="stat-icon primary" style="width:28px;height:28px;font-size:0.85rem;border-radius:8px;">
                        <i class="bi bi-clock-history"></i>
                    </span>
                    <span class="fw-bold">Transaksi Terakhir</span>
                </div>
                <a href="<?= View::url('transactions') ?>" class="btn btn-sm btn-light border btn-pill px-3 py-1">
                    Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Order ID & Ref</th>
                            <th>Platform</th>
                            <th>Gateway & Mode</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentTransactions)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-6 d-block mb-2 text-secondary opacity-50"></i>
                                    Belum ada transaksi pembayaran yang tercatat.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentTransactions as $trx): 
                                $isLive = (($trx['mode'] ?? 'sandbox') === 'live');
                            ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark font-monospace"><?= View::escape($trx['order_id']) ?></div>
                                        <small class="text-muted font-monospace" style="font-size: 0.74rem;"><?= View::escape($trx['trx_reference']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= View::escape($trx['app_name'] ?? 'Biru App') ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge bg-secondary-subtle text-secondary font-monospace text-uppercase"><?= View::escape($trx['gateway']) ?></span>
                                            <?php if ($isLive): ?>
                                                <span class="badge bg-success-subtle text-success font-monospace" style="font-size: 0.65rem;">LIVE</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning-subtle text-warning-emphasis font-monospace" style="font-size: 0.65rem;">SANDBOX</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="fw-bold text-dark">
                                        <?= View::formatRupiah($trx['amount']) ?>
                                    </td>
                                    <td>
                                        <span class="badge-status <?= $trx['status'] ?>">
                                            <?php if ($trx['status'] === 'success'): ?>
                                                <i class="bi bi-check-circle-fill"></i> Sukses
                                            <?php elseif ($trx['status'] === 'pending'): ?>
                                                <i class="bi bi-hourglass-split"></i> Pending
                                            <?php else: ?>
                                                <i class="bi bi-x-circle-fill"></i> <?= ucfirst($trx['status']) ?>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="small text-muted" style="white-space: nowrap;">
                                        <?= date('d M Y, H:i', strtotime($trx['created_at'])) ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= View::url('transactions/' . $trx['id']) ?>" class="btn btn-sm btn-light border btn-pill" title="Lihat Detail Transaksi">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="d-flex flex-column gap-3 h-100">
            <!-- Platform Breakdown Card -->
            <div class="card-custom mb-0 flex-grow-1">
                <div class="card-header justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="stat-icon primary" style="width:28px;height:28px;font-size:0.85rem;border-radius:8px;">
                            <i class="bi bi-app-indicator"></i>
                        </span>
                        <span class="fw-bold">Platform Biru</span>
                    </div>
                    <a href="<?= View::url('applications') ?>" class="btn btn-sm btn-light border btn-pill px-3 py-1">Kelola</a>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush border-0">
                        <?php if (empty($applications)): ?>
                            <li class="list-group-item text-center py-4 text-muted">Belum ada platform didaftarkan.</li>
                        <?php else: ?>
                            <?php foreach ($applications as $app): 
                                $appMode = $app['mode'] ?? 'sandbox';
                                $isAppLive = ($appMode === 'live');
                            ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center py-3 px-4 border-bottom border-light">
                                    <div>
                                        <div class="fw-bold text-dark fs-6"><?= View::escape($app['app_name']) ?></div>
                                        <small class="text-muted font-monospace"><?= View::escape($app['app_code']) ?></small>
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        <?php if ($isAppLive): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 11px;">LIVE</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" style="font-size: 11px;">SANDBOX</span>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Single Gateway Hub Info Card -->
            <div class="card-custom mb-0">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="stat-icon primary" style="width:32px;height:32px;font-size:1rem;border-radius:10px;">
                            <i class="bi bi-shield-check"></i>
                        </span>
                        <h6 class="fw-bold mb-0 text-dark">Pintu Pembayaran Terpadu</h6>
                    </div>
                    <p class="small text-muted mb-3" style="font-size: 12.5px; line-height: 1.5;">
                        Seluruh platform internal Biru terhubung ke payment gateway resmi melalui 1 hub tersentralisasi dengan API & Webhook otomatis.
                    </p>
                    <div class="p-2 px-3 rounded-3 small font-monospace text-truncate mb-3 bg-light border text-muted" style="font-size: 11.5px;">
                        <?= View::url('pay') ?>
                    </div>
                    <a href="<?= View::url('docs') ?>" class="btn btn-sm btn-outline-primary w-100 btn-pill">
                        <i class="bi bi-book me-1"></i> Buka Panduan Integrasi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
