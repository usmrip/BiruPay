<?php
use App\Core\View;
?>

<div class="card-custom mb-4">
    <div class="card-body">
        <form action="<?= View::url('transactions') ?>" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary">Cari Order ID / Pelanggan</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Order ID, Referensi, Nama..." value="<?= View::escape($filters['search'] ?? '') ?>">
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary">Platform / Aplikasi</label>
                <select name="app_id" class="form-select form-select-sm">
                    <option value="">Semua Platform</option>
                    <?php foreach ($applications as $app): ?>
                        <option value="<?= $app['id'] ?>" <?= ($filters['app_id'] ?? '') == $app['id'] ? 'selected' : '' ?>>
                            <?= View::escape($app['app_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary">Payment Gateway</label>
                <select name="gateway" class="form-select form-select-sm">
                    <option value="">Semua Gateway</option>
                    <option value="midtrans" <?= ($filters['gateway'] ?? '') === 'midtrans' ? 'selected' : '' ?>>Midtrans</option>
                    <option value="duitku" <?= ($filters['gateway'] ?? '') === 'duitku' ? 'selected' : '' ?>>Duitku</option>
                    <option value="doku" <?= ($filters['gateway'] ?? '') === 'doku' ? 'selected' : '' ?>>Doku</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label small fw-semibold text-secondary">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="success" <?= ($filters['status'] ?? '') === 'success' ? 'selected' : '' ?>>Sukses (Lunas)</option>
                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="failed" <?= ($filters['status'] ?? '') === 'failed' ? 'selected' : '' ?>>Gagal</option>
                    <option value="expired" <?= ($filters['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <a href="<?= View::url('transactions') ?>" class="btn btn-sm btn-light border" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
                <a href="<?= View::url('transactions/export?' . http_build_query($filters)) ?>" class="btn btn-sm btn-outline-success" title="Export CSV">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> CSV
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card-custom">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold">Total Transaksi Ditemukan: <?= number_format($totalTransactions) ?></span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Order ID & Ref Hub</th>
                    <th>Platform</th>
                    <th>Gateway</th>
                    <th>Nominal</th>
                    <th>Pelanggan</th>
                    <th>Status</th>
                    <th>Notifikasi App</th>
                    <th>Waktu Dibuat</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            Tidak ada data transaksi yang cocok dengan filter.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $trx): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark font-monospace"><?= View::escape($trx['order_id']) ?></div>
                                <small class="text-muted font-monospace" style="font-size: 0.75rem;"><?= View::escape($trx['trx_reference']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= View::escape($trx['app_name'] ?? 'Biru App') ?></span>
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-secondary font-monospace text-uppercase"><?= View::escape($trx['gateway']) ?></span>
                                <?php if (!empty($trx['payment_method'])): ?>
                                    <div class="small text-muted" style="font-size: 0.72rem;"><?= View::escape($trx['payment_method']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-bold text-dark">
                                <?= View::formatRupiah($trx['amount']) ?>
                            </td>
                            <td>
                                <div class="fw-semibold small"><?= View::escape($trx['customer_name'] ?? '-') ?></div>
                                <div class="text-muted" style="font-size: 0.75rem;"><?= View::escape($trx['customer_email'] ?? '') ?></div>
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
                            <td>
                                <?php if ($trx['is_app_notified']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle" title="Webhook sukses ke platform">
                                        <i class="bi bi-check-all"></i> Terkirim
                                    </span>
                                <?php elseif (!empty($trx['app_notify_status'])): ?>
                                    <span class="badge bg-danger-subtle text-danger border" title="<?= View::escape($trx['app_notify_status']) ?>">
                                        <i class="bi bi-exclamation-circle"></i> <?= View::escape($trx['app_notify_status']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="small text-muted">
                                <?= View::formatDateTime($trx['created_at'], $trx['timezone'] ?? 'UTC', 'd/m/Y H:i T') ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= View::url('transactions/' . $trx['id']) ?>" class="btn btn-sm btn-light border" title="Lihat Detail Transaksi">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
            <span class="small text-muted">Halaman <?= $currentPage ?> dari <?= $totalPages ?></span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= View::url('transactions?' . http_build_query(array_merge($filters, ['page' => $currentPage - 1]))) ?>">&laquo;</a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
                        <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= View::url('transactions?' . http_build_query(array_merge($filters, ['page' => $i]))) ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?= View::url('transactions?' . http_build_query(array_merge($filters, ['page' => $currentPage + 1]))) ?>">&raquo;</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    <?php endif; ?>
</div>

