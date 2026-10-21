<?php
use App\Core\View;
$isSuccess = ($trx && $trx['status'] === 'success');
$isPending = ($trx && $trx['status'] === 'pending');
$returnUrl = $app['return_url'] ?? 'https://biru.co.id';
?>

<div class="checkout-card">
    <div class="p-4 text-center <?= $isSuccess ? 'bg-success text-white' : ($isPending ? 'bg-warning text-dark' : 'bg-danger text-white') ?>">
        <div class="mb-2">
            <?php if ($isSuccess): ?>
                <i class="bi bi-check-circle-fill display-3"></i>
            <?php elseif ($isPending): ?>
                <i class="bi bi-hourglass-split display-3"></i>
            <?php else: ?>
                <i class="bi bi-x-circle-fill display-3"></i>
            <?php endif; ?>
        </div>
        <h4 class="fw-bold mb-1">
            <?= $isSuccess ? 'Pembayaran Berhasil Diterima!' : ($isPending ? 'Menunggu Pembayaran' : 'Pembayaran Gagal / Dibatalkan') ?>
        </h4>
        <p class="small mb-0 opacity-75">
            <?= $isSuccess ? 'Transaksi telah diverifikasi dan lunas' : ($isPending ? 'Silakan selesaikan pembayaran sesuai instruksi' : 'Transaksi tidak dapat diproses') ?>
        </p>
    </div>

    <div class="p-4">
        <?php if ($trx): ?>
            <div class="table-responsive mb-4">
                <table class="table table-borderless small mb-0">
                    <tbody>
                        <tr class="border-bottom">
                            <td class="text-muted py-2">Platform / Layanan:</td>
                            <td class="text-end fw-bold py-2"><?= View::escape($app['app_name'] ?? 'Biru') ?></td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted py-2">Order ID:</td>
                            <td class="text-end font-monospace fw-bold py-2"><?= View::escape($trx['order_id']) ?></td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted py-2">Nomor Referensi Hub:</td>
                            <td class="text-end font-monospace py-2"><?= View::escape($trx['trx_reference']) ?></td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted py-2">Payment Gateway:</td>
                            <td class="text-end text-uppercase fw-bold py-2"><?= View::escape($trx['gateway']) ?></td>
                        </tr>
                        <?php if (!empty($trx['payment_method'])): ?>
                            <tr class="border-bottom">
                                <td class="text-muted py-2">Metode Pembayaran:</td>
                                <td class="text-end fw-bold py-2"><?= View::escape($trx['payment_method']) ?></td>
                            </tr>
                        <?php endif; ?>
                        <tr class="border-bottom">
                            <td class="text-muted py-2">Nama Pelanggan:</td>
                            <td class="text-end fw-bold py-2"><?= View::escape($trx['customer_name'] ?? '-') ?></td>
                        </tr>
                        <tr class="border-bottom">
                            <td class="text-muted py-2">Total Pembayaran:</td>
                            <td class="text-end fs-5 fw-bold text-primary py-2"><?= View::formatRupiah($trx['amount']) ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted py-2">Waktu:</td>
                            <td class="text-end py-2"><?= date('d M Y - H:i', strtotime($trx['paid_at'] ?? $trx['created_at'])) ?> WIB</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div class="d-grid gap-2">
            <a href="<?= View::escape($returnUrl) ?>" class="btn btn-primary py-2 fw-bold" style="background-color: #0052cc; border-color: #0052cc;">
                <i class="bi bi-box-arrow-left me-1"></i> Kembali ke <?= View::escape($app['app_name'] ?? 'Aplikasi Biru') ?>
            </a>
            <button onclick="window.print()" class="btn btn-outline-secondary py-2">
                <i class="bi bi-printer me-1"></i> Cetak Bukti Pembayaran
            </button>
        </div>
    </div>
</div>

