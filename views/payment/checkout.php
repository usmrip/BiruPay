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
    <div style="padding: 32px 24px 20px; text-align: center;">
        <div style="width: 44px; height: 44px; margin: 0 auto 12px; background: #000000; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #ffffff; font-weight: 700; font-size: 18px;">
            <?= strtoupper(substr($app['app_name'] ?? 'B', 0, 1)) ?>
        </div>
        <div style="font-size: 15px; font-weight: 600; color: #1d1d1f; letter-spacing: -0.01em;">
            <?= View::escape($app['app_name'] ?? 'Biru Pay') ?>
        </div>
        <div style="font-size: 12px; color: #86868b; margin-top: 2px;">
            Order #<?= View::escape($trx['order_id']) ?>
        </div>

        <!-- Total Tagihan -->
        <div style="margin-top: 24px;">
            <div style="font-size: 12px; font-weight: 500; color: #86868b; text-transform: uppercase; letter-spacing: 0.04em;">
                Total Tagihan
            </div>
            <div style="font-size: 36px; font-weight: 700; color: #1d1d1f; letter-spacing: -0.03em; margin-top: 4px;">
                <?= View::formatRupiah($trx['amount']) ?>
            </div>
        </div>
    </div>

    <!-- Divider -->
    <div style="height: 1px; background: rgba(0,0,0,0.06); margin: 0 24px;"></div>

    <!-- Body Content -->
    <div style="padding: 24px;">
        <?php if ($isSuccess): ?>
            <!-- Status Sudah Lunas -->
            <div style="text-align: center; padding: 12px 0 20px;">
                <div style="width: 52px; height: 52px; margin: 0 auto 14px; background: rgba(52, 199, 89, 0.12); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #34c759; font-size: 26px;">
                    <i class="bi bi-check2"></i>
                </div>
                <div style="font-size: 17px; font-weight: 600; color: #1d1d1f;">
                    Pembayaran Telah Selesai
                </div>
                <div style="font-size: 13px; color: #86868b; margin-top: 4px;">
                    Transaksi ini sudah diverifikasi lunas.
                </div>
            </div>

            <a href="<?= View::url('payment/return?ref=' . urlencode($trx['trx_reference'])) ?>" class="btn-apple">
                Lihat Bukti Pembayaran
            </a>
        <?php else: ?>
            <!-- Rincian Transaksi -->
            <table class="table-summary" style="margin-bottom: 24px;">
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
                        <td class="label">No. Telepon</td>
                        <td class="value"><?= View::escape($trx['customer_phone']) ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td class="label">Waktu Dibuat</td>
                        <td class="value"><?= View::formatDateTime($trx['created_at'], $appTimezone) ?></td>
                    </tr>
                    <tr>
                        <td class="label">No. Referensi</td>
                        <td class="value" style="font-family: monospace; font-size: 12px;"><?= View::escape($trx['trx_reference']) ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- Metode Pembayaran yang Didukung (Muted Badges) -->
            <div style="margin-bottom: 24px; text-align: center;">
                <div style="font-size: 11px; font-weight: 500; color: #86868b; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 10px;">
                    Metode Pembayaran Tersedia
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 6px; justify-content: center;">
                    <span class="pill-badge">QRIS</span>
                    <span class="pill-badge">BCA Virtual Account</span>
                    <span class="pill-badge">Mandiri VA</span>
                    <span class="pill-badge">BRI / BNI VA</span>
                    <span class="pill-badge">GoPay / ShopeePay</span>
                    <span class="pill-badge">Kartu Kredit</span>
                </div>
            </div>

            <!-- Tombol Bayar Apple Style -->
            <button id="btn-pay" class="btn-apple" type="button">
                <span>Bayar Sekarang</span>
            </button>

            <!-- Loading State Hidden -->
            <div id="pay-loading" style="display: none; text-align: center; padding: 14px 0; font-size: 14px; color: #86868b;">
                <span class="spinner-border spinner-border-sm me-2" role="status"></span> Menghubungkan sistem pembayaran...
            </div>
        <?php endif; ?>

        <!-- Footer Security -->
        <div style="margin-top: 24px; text-align: center; font-size: 11px; color: #86868b; display: flex; align-items: center; justify-content: center; gap: 6px;">
            <i class="bi bi-shield-lock-fill" style="color: #34c759;"></i>
            <span>Diverifikasi aman & dienkripsi dengan standar 256-bit</span>
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
