<?php
use App\Core\View;

$isSuccess = ($trx && $trx['status'] === 'success');
$isPending = ($trx && $trx['status'] === 'pending');
$returnUrl = $app['return_url'] ?? 'https://biru.co.id';
$appTimezone = $app['timezone'] ?? 'UTC';
?>

<div class="apple-card">
    <!-- Header Status -->
    <div style="padding: 36px 24px 20px; text-align: center;">
        <?php if ($isSuccess): ?>
            <div style="width: 56px; height: 56px; margin: 0 auto 16px; background: rgba(52, 199, 89, 0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #34c759; font-size: 28px;">
                <i class="bi bi-check2"></i>
            </div>
            <div style="font-size: 20px; font-weight: 700; color: #1d1d1f; letter-spacing: -0.02em;">
                Pembayaran Berhasil
            </div>
            <div style="font-size: 13px; color: #86868b; margin-top: 4px;">
                Transaksi telah terverifikasi dan selesai diproses
            </div>
        <?php elseif ($isPending): ?>
            <div style="width: 56px; height: 56px; margin: 0 auto 16px; background: rgba(255, 149, 0, 0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ff9500; font-size: 24px;">
                <i class="bi bi-clock-history"></i>
            </div>
            <div style="font-size: 20px; font-weight: 700; color: #1d1d1f; letter-spacing: -0.02em;">
                Menunggu Pembayaran
            </div>
            <div style="font-size: 13px; color: #86868b; margin-top: 4px;">
                Silakan selesaikan pembayaran tagihan Anda
            </div>
        <?php else: ?>
            <div style="width: 56px; height: 56px; margin: 0 auto 16px; background: rgba(255, 59, 48, 0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #ff3b30; font-size: 26px;">
                <i class="bi bi-x-lg"></i>
            </div>
            <div style="font-size: 20px; font-weight: 700; color: #1d1d1f; letter-spacing: -0.02em;">
                Pembayaran Tidak Berhasil
            </div>
            <div style="font-size: 13px; color: #86868b; margin-top: 4px;">
                Transaksi dibatalkan atau telah kadaluarsa
            </div>
        <?php endif; ?>

        <!-- Nominal -->
        <?php if ($trx): ?>
            <div style="margin-top: 24px;">
                <div style="font-size: 12px; font-weight: 500; color: #86868b; text-transform: uppercase; letter-spacing: 0.04em;">
                    Jumlah Dibayar
                </div>
                <div style="font-size: 34px; font-weight: 700; color: #1d1d1f; letter-spacing: -0.03em; margin-top: 4px;">
                    <?= View::formatRupiah($trx['amount']) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Divider -->
    <div style="height: 1px; background: rgba(0,0,0,0.06); margin: 0 24px;"></div>

    <!-- Details -->
    <div style="padding: 24px;">
        <?php if ($trx): ?>
            <table class="table-summary" style="margin-bottom: 24px;">
                <tbody>
                    <tr>
                        <td class="label">Layanan</td>
                        <td class="value"><?= View::escape($app['app_name'] ?? 'Biru') ?></td>
                    </tr>
                    <tr>
                        <td class="label">No. Pesanan</td>
                        <td class="value"><?= View::escape($trx['order_id']) ?></td>
                    </tr>
                    <tr>
                        <td class="label">No. Referensi</td>
                        <td class="value" style="font-family: monospace; font-size: 12px;"><?= View::escape($trx['trx_reference']) ?></td>
                    </tr>
                    <?php if (!empty($trx['payment_method'])): ?>
                    <tr>
                        <td class="label">Metode Pembayaran</td>
                        <td class="value" style="text-transform: uppercase;"><?= View::escape($trx['payment_method']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="label">Waktu Transaksi</td>
                        <td class="value"><?= View::formatDateTime($trx['paid_at'] ?? $trx['created_at'], $appTimezone) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Pelanggan</td>
                        <td class="value"><?= View::escape($trx['customer_name'] ?? '-') ?></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Action Buttons -->
        <a href="<?= View::escape($returnUrl) ?>" class="btn-apple">
            Kembali ke <?= View::escape($app['app_name'] ?? 'Aplikasi') ?>
        </a>

        <button onclick="window.print()" class="btn-apple-secondary" type="button">
            <i class="bi bi-printer me-2"></i> Cetak Tanda Terima
        </button>
    </div>
</div>
