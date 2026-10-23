<?php
use App\Core\View;
use App\Core\Session;
$jam = (int) date('G');
$salam = $jam < 11 ? 'Selamat pagi' : ($jam < 15 ? 'Selamat siang' : ($jam < 19 ? 'Selamat sore' : 'Selamat malam'));
?>

<div class="page-greeting mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
    <div>
        <h4><?= $salam ?>, <?= View::escape(Session::get('user_name', 'Admin')) ?></h4>
        <p>Ringkasan aktivitas pembayaran dari semua platform Biru.</p>
    </div>
    <a href="<?= View::url('transactions') ?>" class="btn btn-primary btn-sm px-3">
        <i class="bi bi-receipt me-1"></i> Laporan Transaksi
    </a>
</div>

<!-- Statistics Row -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <div class="stat-value"><?= View::formatRupiah($stats['total_volume'] ?? 0) ?></div>
                <div class="stat-label">Total Volume Transaksi</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon success">
                <i class="bi bi-check2-circle"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($stats['total_success'] ?? 0) ?></div>
                <div class="stat-label">Transaksi Berhasil</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon warning">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <div class="stat-value"><?= number_format($stats['total_pending'] ?? 0) ?></div>
                <div class="stat-label">Menunggu Pembayaran</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stat-card">
            <div class="stat-icon primary">
                <i class="bi bi-calendar2-check"></i>
            </div>
            <div>
                <div class="stat-value"><?= View::formatRupiah($stats['today_volume'] ?? 0) ?></div>
                <div class="stat-label">Pemasukan Hari Ini (<?= $stats['today_success'] ?? 0 ?> trx)</div>
            </div>
        </div>
    </div>
</div>

<!-- Gateway Status Cards -->
<div class="row g-3 mb-4">
    <?php foreach ($gateways as $gw): ?>
        <div class="col-md-4">
            <div class="card-custom h-100 mb-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="stat-icon primary" style="width:36px;height:36px;font-size:1rem;border-radius:10px;">
                                <i class="bi bi-credit-card-2-front"></i>
                            </span>
                            <h6 class="mb-0 fw-bold"><?= View::escape($gw['gateway_name']) ?></h6>
                        </div>
                        <?php if ($gw['is_active']): ?>
                            <span class="badge bg-success-subtle text-success-emphasis">Aktif</span>
                        <?php else: ?>
                            <span class="badge bg-secondary-subtle text-secondary-emphasis">Nonaktif</span>
                        <?php endif; ?>
                    </div>
                    <div class="gw-row">
                        <span>Mode</span>
                        <strong><?= $gw['is_production'] ? 'Production (Live)' : 'Sandbox (Testing)' ?></strong>
                    </div>
                    <div class="gw-row mb-3">
                        <span>Integrasi</span>
                        <strong>
                            <?php if ($gw['gateway_code'] === 'midtrans'): ?>
                                Snap Popup (Modal)
                            <?php elseif ($gw['gateway_code'] === 'duitku'): ?>
                                Pop Modal / Popup
                            <?php else: ?>
                                Doku Checkout
                            <?php endif; ?>
                        </strong>
                    </div>
                    <a href="<?= View::url('gateways') ?>" class="btn btn-sm btn-outline-primary w-100">
                        <i class="bi bi-sliders me-1"></i> Atur Gateway
                    </a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Recent Transactions & Applications breakdown -->
<div class="row g-3">
    <div class="col-lg-8">
        <div class="card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-1 text-primary"></i> Transaksi Terakhir</span>
                <a href="<?= View::url('transactions') ?>" class="btn btn-sm btn-light border text-primary">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Platform</th>
                            <th>Gateway</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentTransactions)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-1"></i>
                                    Belum ada data transaksi yang tercatat.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentTransactions as $trx): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark font-monospace"><?= View::escape($trx['order_id']) ?></div>
                                        <small class="text-muted font-monospace" style="font-size: 0.75rem;"><?= View::escape($trx['trx_reference']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= View::escape($trx['app_name'] ?? 'Biru App') ?></span>
                                    </td>
                                    <td>
                                        <span class="text-uppercase fw-semibold small"><?= View::escape($trx['gateway']) ?></span>
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
                                    <td class="small text-muted">
                                        <?= date('d/m/Y H:i', strtotime($trx['created_at'])) ?>
                                    </td>
                                    <td>
                                        <a href="<?= View::url('transactions/' . $trx['id']) ?>" class="btn btn-sm btn-light border" title="Detail">
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
        <div class="card-custom">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-layers me-1 text-primary"></i> Platform Biru Terdaftar</span>
                <a href="<?= View::url('applications') ?>" class="btn btn-sm btn-light border text-primary">Kelola</a>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <?php if (empty($applications)): ?>
                        <li class="list-group-item text-center py-4 text-muted">Belum ada platform didaftarkan.</li>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold text-dark"><?= View::escape($app['app_name']) ?></div>
                                    <small class="text-muted font-monospace"><?= View::escape($app['app_code']) ?></small>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                    Aktif
                                </span>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="card-custom">
            <div class="card-header">
                <span><i class="bi bi-shield-check me-1 text-primary"></i> Pintu Pembayaran Terpusat</span>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Semua transaksi dari platform Biru masuk melalui 1 pintu dengan 1 akun & project di masing-masing payment gateway.
                </p>
                <div class="p-2 rounded-3 small font-monospace text-truncate mb-3" style="background:#f6f8fb;border:1px solid #e8ecf2;">
                    <?= View::url('pay') ?>
                </div>
                <a href="<?= View::url('docs') ?>" class="btn btn-sm btn-outline-primary w-100">
                    <i class="bi bi-book me-1"></i> Buka Panduan Integrasi
                </a>
            </div>
        </div>
    </div>
</div>

