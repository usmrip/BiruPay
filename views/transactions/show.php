<?php
use App\Core\View;
?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-custom">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 rounded bg-primary-subtle text-primary"><i class="bi bi-receipt"></i></span>
                    <div>
                        <h6 class="mb-0 fw-bold">Detail Transaksi #<?= View::escape($trx['order_id']) ?></h6>
                        <small class="text-muted font-monospace">Ref Hub: <?= View::escape($trx['trx_reference']) ?></small>
                    </div>
                </div>
                <span class="badge-status <?= $trx['status'] ?>">
                    <?php if ($trx['status'] === 'success'): ?>
                        <i class="bi bi-check-circle-fill"></i> Sukses (Lunas)
                    <?php elseif ($trx['status'] === 'pending'): ?>
                        <i class="bi bi-hourglass-split"></i> Pending
                    <?php else: ?>
                        <i class="bi bi-x-circle-fill"></i> <?= ucfirst($trx['status']) ?>
                    <?php endif; ?>
                </span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th class="bg-light text-secondary" style="width: 30%;">Platform / Aplikasi</th>
                                <td>
                                    <span class="fw-bold text-dark"><?= View::escape($trx['app_name'] ?? 'Biru Platform') ?></span>
                                    <span class="badge bg-light text-secondary border font-monospace ms-2"><?= View::escape($trx['app_code'] ?? '-') ?></span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light text-secondary">Nominal Transaksi</th>
                                <td class="fs-5 fw-bold text-primary"><?= View::formatRupiah($trx['amount']) ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-secondary">Payment Gateway</th>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace text-uppercase fs-6">
                                        <?= View::escape($trx['gateway']) ?>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light text-secondary">Metode / Saluran Pembayaran</th>
                                <td><?= View::escape($trx['payment_method'] ?? 'Belum dipilih / ditagihkan') ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-secondary">ID Transaksi Gateway (Ref)</th>
                                <td class="font-monospace small"><?= View::escape($trx['gateway_reference'] ?? '-') ?></td>
                            </tr>
                            <?php if (!empty($trx['snap_token'])): ?>
                                <tr>
                                    <th class="bg-light text-secondary">Snap Token (Midtrans)</th>
                                    <td class="font-monospace small text-truncate" style="max-width: 350px;">
                                        <?= View::escape($trx['snap_token']) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr>
                                <th class="bg-light text-secondary">Waktu Pembuatan</th>
                                <td><?= View::formatDateTime($trx['created_at'], $app['timezone'] ?? 'UTC') ?></td>
                            </tr>
                            <tr>
                                <th class="bg-light text-secondary">Waktu Lunas (Settled)</th>
                                <td><?= !empty($trx['paid_at']) ? View::formatDateTime($trx['paid_at'], $app['timezone'] ?? 'UTC') : '-' ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h6 class="fw-bold mt-4 mb-3"><i class="bi bi-person me-1 text-primary"></i> Data Pelanggan</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Nama Pelanggan</label>
                        <div class="fw-semibold"><?= View::escape($trx['customer_name'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Email Pelanggan</label>
                        <div class="fw-semibold"><?= View::escape($trx['customer_email'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Nomor Telepon</label>
                        <div class="fw-semibold"><?= View::escape($trx['customer_phone'] ?? '-') ?></div>
                    </div>
                </div>

                <?php if (!empty($trx['webhook_response_raw'])): ?>
                    <h6 class="fw-bold mt-4 mb-2"><i class="bi bi-code-square me-1 text-primary"></i> Raw Webhook Payload dari Gateway</h6>
                    <pre class="code-box"><?= View::escape(json_encode(json_decode($trx['webhook_response_raw']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: $trx['webhook_response_raw']) ?></pre>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between">
                <a href="<?= View::url('transactions') ?>" class="btn btn-light border">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Transaksi
                </a>

                <?php if ($trx['status'] !== 'success'): ?>
                    <form action="<?= View::url('transactions/' . $trx['id'] . '/mark-as-paid') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menandai transaksi ini Lunas secara manual? Notifikasi Telegram & Email serta Webhook ke aplikasi akan otomatis dikirimkan.');">
                        <?= View::csrfField() ?>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check2-circle me-1"></i> Tandai Sukses / Lunas Manual
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Webhook Forwarding Status to Application -->
    <div class="col-lg-4">
        <div class="card-custom">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold"><i class="bi bi-send-check me-1 text-primary"></i> Penerusan Notifikasi ke Platform</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label small text-muted">Status Notifikasi ke Platform:</label>
                    <div>
                        <?php if ($trx['is_app_notified']): ?>
                            <span class="badge bg-success-subtle text-success border border-success-subtle p-2">
                                <i class="bi bi-check-circle-fill me-1"></i> Berhasil Diteruskan
                            </span>
                        <?php else: ?>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle p-2">
                                <i class="bi bi-clock-history me-1"></i> <?= View::escape($trx['app_notify_status'] ?? 'Belum Diteruskan') ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted">Percobaan Pengiriman:</label>
                    <div class="fw-bold"><?= (int)($trx['app_notify_attempts'] ?? 0) ?> kali</div>
                </div>

                <div class="mb-3">
                    <label class="form-label small text-muted">URL Webhook Platform:</label>
                    <div class="p-2 bg-light rounded border small font-monospace text-break">
                        <?= View::escape($trx['webhook_url'] ?? 'Tidak dikonfigurasi') ?>
                    </div>
                </div>

                <form action="<?= View::url('transactions/' . $trx['id'] . '/resend-webhook') ?>" method="POST">
                    <?= View::csrfField() ?>
                    <button type="submit" class="btn btn-outline-primary w-100" <?= empty($trx['webhook_url']) ? 'disabled' : '' ?>>
                        <i class="bi bi-arrow-repeat me-1"></i> Kirim Ulang Webhook ke Platform
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

