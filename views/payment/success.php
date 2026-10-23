<?php
use App\Core\View;

$isSuccess = ($trx && $trx['status'] === 'success');
$isPending = ($trx && $trx['status'] === 'pending');
$returnUrl = $app['return_url'] ?? 'https://biru.co.id';
$appTimezone = $app['timezone'] ?? 'UTC';

$finalReturnUrl = $returnUrl;
if (!empty($trx['order_id']) || !empty($trx['trx_reference'])) {
    $params = [];
    if (!empty($trx['order_id'])) {
        $params['order_id'] = $trx['order_id'];
    }
    if (!empty($trx['trx_reference'])) {
        $params['ref'] = $trx['trx_reference'];
    }
    $query = http_build_query($params);
    $finalReturnUrl .= (strpos($finalReturnUrl, '?') === false ? '?' : '&') . $query;
}
?>

<div class="apple-card">
    <div class="bp-head">
        <?php if ($isSuccess): ?>
            <div class="bp-status-icon ok"><i class="bi bi-check2"></i></div>
            <div class="bp-status-title">Pembayaran Berhasil</div>
            <div class="bp-status-text">Transaksi telah terverifikasi dan selesai diproses</div>
        <?php elseif ($isPending): ?>
            <div class="bp-status-icon wait"><i class="bi bi-clock-history"></i></div>
            <div class="bp-status-title">Menunggu Pembayaran</div>
            <div class="bp-status-text">Silakan selesaikan pembayaran tagihan Anda</div>
        <?php else: ?>
            <div class="bp-status-icon fail"><i class="bi bi-x-lg"></i></div>
            <div class="bp-status-title">Pembayaran Tidak Berhasil</div>
            <div class="bp-status-text">Transaksi dibatalkan atau telah kedaluwarsa</div>
        <?php endif; ?>

        <?php if ($trx): ?>
            <div class="bp-amount-label"><?= $isSuccess ? 'Jumlah Dibayar' : 'Total Tagihan' ?></div>
            <div class="bp-amount"><?= View::formatRupiah($trx['amount']) ?></div>
        <?php endif; ?>
    </div>

    <div class="bp-divider"></div>

    <div class="bp-body">
        <?php if ($trx): ?>
            <table class="table-summary" style="margin-bottom: 22px;">
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
                        <td class="label">Referensi</td>
                        <td class="value" style="font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12.5px;"><?= View::escape($trx['trx_reference']) ?></td>
                    </tr>
                    <?php if (!empty($trx['payment_method'])): ?>
                    <tr>
                        <td class="label">Metode</td>
                        <td class="value" style="text-transform: uppercase;"><?= View::escape($trx['payment_method']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="label">Waktu</td>
                        <td class="value"><?= View::formatDateTime($trx['paid_at'] ?? $trx['created_at'], $appTimezone) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Pelanggan</td>
                        <td class="value"><?= View::escape($trx['customer_name'] ?? '-') ?></td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>

        <a href="<?= View::escape($finalReturnUrl) ?>" class="btn-apple">
            Kembali ke <?= View::escape($app['app_name'] ?? 'Aplikasi') ?>
        </a>

        <button onclick="window.print()" class="btn-apple-secondary" type="button">
            <i class="bi bi-printer me-2"></i> Cetak Tanda Terima
        </button>
    </div>
</div>
