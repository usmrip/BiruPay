<?php
use App\Core\View;

$activeGateway = $trx['gateway'] ?? 'midtrans';
$gwConfig = $gatewayConfigs[$activeGateway] ?? null;
$appTimezone = $app['timezone'] ?? 'UTC';
$isSuccess = ($trx['status'] === 'success');

$redirectPaymentUrl = '';
if ($activeGateway === 'duitku' && !empty($trx['gateway_reference'])) {
    $isSand = (strpos($trx['gateway_reference'], 'DS') === 0 || empty($gwConfig['is_production']));
    $redirectPaymentUrl = $isSand 
        ? 'https://app-sandbox.duitku.com/redirect_checkout?reference=' . urlencode($trx['gateway_reference'])
        : 'https://app-prod.duitku.com/redirect_checkout?reference=' . urlencode($trx['gateway_reference']);
} elseif ($activeGateway === 'midtrans' && !empty($trx['snap_token'])) {
    $redirectPaymentUrl = empty($gwConfig['is_production'])
        ? 'https://app.sandbox.midtrans.com/snap/v4/redirection/' . urlencode($trx['snap_token'])
        : 'https://app.midtrans.com/snap/v4/redirection/' . urlencode($trx['snap_token']);
} elseif ($activeGateway === 'doku' && !empty($trx['gateway_reference'])) {
    $redirectPaymentUrl = $trx['gateway_reference'];
}
?>

<!-- Load Payment Script in Background -->
<?php if ($activeGateway === 'midtrans' && !empty($gwConfig['script_url'])): ?>
    <script src="<?= $gwConfig['script_url'] ?>" data-client-key="<?= $gwConfig['client_key'] ?? '' ?>"></script>
<?php elseif ($activeGateway === 'duitku' && !empty($gwConfig['script_url'])): ?>
    <script src="<?= $gwConfig['script_url'] ?>"></script>
<?php endif; ?>

<div class="apple-card">
    <!-- Header -->
    <div class="bp-head">
        <div class="bp-logo"><?= strtoupper(substr($app['app_name'] ?? 'B', 0, 1)) ?></div>
        <div class="bp-app"><?= View::escape($app['app_name'] ?? 'Biru Pay') ?></div>
        <div class="bp-sub">Pesanan #<?= View::escape($trx['order_id']) ?></div>

        <div class="bp-amount-label">Total Tagihan</div>
        <div class="bp-amount"><?= View::formatRupiah($trx['amount']) ?></div>
    </div>

    <div class="bp-divider"></div>

    <div class="bp-body">
        <?php if ($isSuccess): ?>
            <div style="text-align:center; padding: 4px 0 22px;">
                <div class="bp-status-icon ok"><i class="bi bi-check2"></i></div>
                <div class="bp-status-title">Pembayaran Selesai</div>
                <div class="bp-status-text">Transaksi ini sudah terverifikasi lunas.</div>
            </div>
            <a href="<?= View::url('payment/return?ref=' . urlencode($trx['trx_reference'])) ?>" class="btn-apple">
                Lihat Bukti Pembayaran
            </a>
        <?php else: ?>
            <table class="table-summary" style="margin-bottom: 22px;">
                <tbody>
                    <tr>
                        <td class="label">Pelanggan</td>
                        <td class="value"><?= View::escape($trx['customer_name'] ?? 'Pelanggan') ?></td>
                    </tr>
                    <tr>
                        <td class="label">Email</td>
                        <td class="value"><?= View::escape($trx['customer_email'] ?? '-') ?></td>
                    </tr>
                    <?php if (!empty($trx['customer_phone'])): ?>
                    <tr>
                        <td class="label">Telepon</td>
                        <td class="value"><?= View::escape($trx['customer_phone']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="label">Dibuat</td>
                        <td class="value"><?= View::formatDateTime($trx['created_at'], $appTimezone) ?></td>
                    </tr>
                    <tr>
                        <td class="label">Referensi</td>
                        <td class="value" style="font-family: ui-monospace, SFMono-Regular, Menlo, monospace; font-size: 12.5px;"><?= View::escape($trx['trx_reference']) ?></td>
                    </tr>
                </tbody>
            </table>

            <div style="margin-bottom: 22px;">
                <div class="bp-pay-title">Metode pembayaran tersedia</div>
                <div class="bp-pills">
                    <span class="pill-badge">QRIS</span>
                    <span class="pill-badge">Virtual Account</span>
                    <span class="pill-badge">E-Wallet</span>
                    <span class="pill-badge">Kartu Kredit</span>
                </div>
            </div>

            <button id="btn-pay" class="btn-apple" type="button">
                <i class="bi bi-lock-fill"></i><span>Bayar Sekarang</span>
            </button>

            <div id="pay-loading" class="bp-loading">
                <span class="spinner-border spinner-border-sm me-2" role="status"></span> Menghubungkan sistem pembayaran...
            </div>
        <?php endif; ?>

        <div class="bp-secure">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Pembayaran aman &amp; terenkripsi</span>
        </div>
    </div>
</div>

<?php if (!$isSuccess): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnPay = document.getElementById('btn-pay');
    const loadingState = document.getElementById('pay-loading');
    const trxRef = <?= json_encode($trx['trx_reference']) ?>;
    const gateway = <?= json_encode($activeGateway) ?>;
    let snapToken = <?= json_encode($trx['snap_token'] ?? '') ?>;
    let gatewayRef = <?= json_encode($trx['gateway_reference'] ?? '') ?>;
    let redirectUrl = <?= json_encode($redirectPaymentUrl) ?>;
    const returnUrl = <?= json_encode(View::url('payment/return?ref=' . urlencode($trx['trx_reference']))) ?>;

    function openGatewayPopup() {
        if (gateway === 'midtrans') {
            if (typeof window.snap !== 'undefined' && snapToken) {
                window.snap.pay(snapToken, {
                    onSuccess: function () { window.location.href = returnUrl; },
                    onPending: function () { window.location.href = returnUrl; },
                    onError: function () {
                        if (redirectUrl) { window.location.href = redirectUrl; return; }
                        alert('Pembayaran dibatalkan atau gagal diproses.'); resetBtn(); 
                    },
                    onClose: function () { resetBtn(); }
                });
                return;
            } else if (redirectUrl) {
                window.location.href = redirectUrl;
                return;
            }
        } else if (gateway === 'duitku') {
            if (typeof checkout !== 'undefined' && gatewayRef) {
                try {
                    checkout.process(gatewayRef, {
                        successEvent: function () { window.location.href = returnUrl; },
                        pendingEvent: function () { window.location.href = returnUrl; },
                        errorEvent: function () {
                            if (redirectUrl) { window.location.href = redirectUrl; return; }
                            alert('Pembayaran dibatalkan atau gagal diproses.'); resetBtn(); 
                        },
                        closeEvent: function () { resetBtn(); }
                    });
                    return;
                } catch(e) {
                    console.warn('Duitku popup error, falling back:', e);
                    if (redirectUrl) { window.location.href = redirectUrl; return; }
                }
            } else if (redirectUrl) {
                window.location.href = redirectUrl;
                return;
            }
        } else if (gateway === 'doku') {
            if (redirectUrl) {
                window.location.href = redirectUrl;
                return;
            }
        }

        // Fallback: fetch/initiate token dynamically via fetch
        fetch('<?= View::url('payment/initiate') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ ref: trxRef, gateway: gateway })
        })
        .then(function (res) { return res.json(); })
        .then(function (res) {
            if (res.success) {
                if (gateway === 'midtrans' && res.snap_token) {
                    snapToken = res.snap_token;
                    if (typeof window.snap !== 'undefined') {
                        window.snap.pay(snapToken, {
                            onSuccess: function () { window.location.href = returnUrl; },
                            onPending: function () { window.location.href = returnUrl; },
                            onError: function () { alert('Pembayaran gagal.'); resetBtn(); },
                            onClose: function () { resetBtn(); }
                        });
                    } else if (res.payment_url) {
                        window.location.href = res.payment_url;
                    }
                } else if (gateway === 'duitku' && res.reference) {
                    gatewayRef = res.reference;
                    const duitkuUrl = res.payment_url;
                    if (typeof checkout !== 'undefined') {
                        try {
                            checkout.process(gatewayRef, {
                                successEvent: function () { window.location.href = returnUrl; },
                                pendingEvent: function () { window.location.href = returnUrl; },
                                errorEvent: function () {
                                    if (duitkuUrl) window.location.href = duitkuUrl;
                                    else { alert('Pembayaran gagal.'); resetBtn(); }
                                },
                                closeEvent: function () { resetBtn(); }
                            });
                        } catch(e) {
                            if (duitkuUrl) window.location.href = duitkuUrl;
                            else { alert('Gagal memuat popup Duitku.'); resetBtn(); }
                        }
                    } else if (duitkuUrl) {
                        window.location.href = duitkuUrl;
                    }
                } else if (res.payment_url) {
                    window.location.href = res.payment_url;
                } else {
                    alert(res.message || 'Gagal memuat transaksi.');
                    resetBtn();
                }
            } else {
                alert(res.message || 'Gagal memproses tagihan.');
                resetBtn();
            }
        })
        .catch(function () {
            alert('Koneksi terputus. Silakan coba kembali.');
            resetBtn();
        });
    }

    function resetBtn() {
        btnPay.style.display = 'flex';
        loadingState.style.display = 'none';
    }

    btnPay.addEventListener('click', function () {
        btnPay.style.display = 'none';
        loadingState.style.display = 'block';
        openGatewayPopup();
    });
});
</script>
<?php endif; ?>
