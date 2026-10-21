<?php
use App\Core\View;

$midtransConfig = $gatewayConfigs['midtrans'] ?? null;
$duitkuConfig   = $gatewayConfigs['duitku'] ?? null;
$dokuConfig     = $gatewayConfigs['doku'] ?? null;
$activeGatewayCode = $trx['gateway'] ?? 'midtrans';
?>

<!-- Include Payment Gateway Popup Scripts -->
<?php if ($midtransConfig): ?>
    <script src="<?= $midtransConfig['script_url'] ?>" data-client-key="<?= $midtransConfig['client_key'] ?>"></script>
<?php endif; ?>

<?php if ($duitkuConfig): ?>
    <script src="<?= $duitkuConfig['script_url'] ?>"></script>
<?php endif; ?>

<div class="checkout-card">
    <!-- Header -->
    <div class="p-4 text-center text-white" style="background: linear-gradient(135deg, #0052cc, #0040a8);">
        <div class="brand-logo mx-auto mb-2" style="width: 44px; height: 44px; background: rgba(255, 255, 255, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 1.3rem;">B</div>
        <h5 class="fw-bold mb-1"><?= View::escape($app['app_name'] ?? 'Biru Indonesia Creative') ?></h5>
        <p class="small mb-0 opacity-75">Sistem Pembayaran Terpadu</p>
    </div>

    <!-- Amount Display -->
    <div class="p-4 bg-light text-center border-bottom">
        <span class="text-secondary small fw-semibold text-uppercase">Total Tagihan</span>
        <div class="display-6 fw-bold text-dark mt-1"><?= View::formatRupiah($trx['amount']) ?></div>
        <div class="small text-muted mt-1 font-monospace">Order #<?= View::escape($trx['order_id']) ?> &bull; Ref: <?= View::escape($trx['trx_reference']) ?></div>
    </div>

    <!-- Checkout Body -->
    <div class="p-4">
        <!-- Customer summary -->
        <div class="mb-4">
            <h6 class="fw-bold text-dark mb-2 small text-uppercase" style="letter-spacing: 0.05em;">Informasi Pelanggan</h6>
            <div class="p-3 bg-light rounded-3 small">
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Nama:</span>
                    <strong class="text-dark"><?= View::escape($trx['customer_name'] ?? 'Pelanggan') ?></strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="text-muted">Email:</span>
                    <strong class="text-dark"><?= View::escape($trx['customer_email'] ?? '-') ?></strong>
                </div>
                <?php if (!empty($trx['customer_phone'])): ?>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">No. HP:</span>
                        <strong class="text-dark"><?= View::escape($trx['customer_phone']) ?></strong>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($trx['status'] === 'success'): ?>
            <div class="alert alert-success text-center py-4 mb-0">
                <i class="bi bi-check-circle-fill display-4 text-success d-block mb-2"></i>
                <h5 class="fw-bold text-success mb-1">Pembayaran Telah Selesai</h5>
                <p class="small text-muted mb-3">Transaksi ini sudah lunas.</p>
                <a href="<?= View::url('payment/return?ref=' . urlencode($trx['trx_reference'])) ?>" class="btn btn-success">
                    Lihat Bukti Pembayaran
                </a>
            </div>
        <?php else: ?>
            <!-- Gateway Selection -->
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-2 small text-uppercase" style="letter-spacing: 0.05em;">Pilih Metode / Saluran Pembayaran</h6>
                
                <?php if (empty($gateways)): ?>
                    <div class="alert alert-warning small">
                        <i class="bi bi-exclamation-triangle me-1"></i> Belum ada payment gateway yang aktif. Hubungi administrator.
                    </div>
                <?php else: ?>
                    <div class="gateway-list">
                        <?php foreach ($gateways as $index => $gw): ?>
                            <?php $isSelected = ($gw['gateway_code'] === $activeGatewayCode) || ($index === 0 && empty($activeGatewayCode)); ?>
                            <div class="gateway-selector-item <?= $isSelected ? 'selected' : '' ?>" data-gateway="<?= $gw['gateway_code'] ?>">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input mt-0" type="radio" name="gateway_choice" value="<?= $gw['gateway_code'] ?>" <?= $isSelected ? 'checked' : '' ?>>
                                    <div>
                                        <div class="fw-bold text-dark"><?= View::escape($gw['gateway_name']) ?></div>
                                        <small class="text-muted">
                                            <?php if ($gw['gateway_code'] === 'midtrans'): ?>
                                                Popup Snap (GoPay, QRIS, Virtual Account, Kartu Kredit)
                                            <?php elseif ($gw['gateway_code'] === 'duitku'): ?>
                                                Duitku Pop (OVO, DANA, ShopeePay, Virtual Account)
                                            <?php else: ?>
                                                DOKU Checkout
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                </div>
                                <span class="badge bg-light text-secondary border">Metode Popup</span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submit Pay Button -->
            <button type="button" id="btn-pay-now" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm fs-5" style="background-color: #0052cc; border-color: #0052cc;">
                <i class="bi bi-shield-lock-fill me-1"></i> Bayar Sekarang
            </button>

            <div class="text-center mt-3">
                <small class="text-muted d-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-shield-check text-success"></i> Transaksi Aman & Terenkripsi oleh Biru PayHub
                </small>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$extraScripts = "
<script>
$(document).ready(function() {
    let currentGateway = '" . $activeGatewayCode . "';
    let snapToken = '" . ($trx['snap_token'] ?? '') . "';
    let duitkuReference = '" . ($trx['gateway_reference'] ?? '') . "';
    const trxReference = '" . $trx['trx_reference'] . "';
    const finishUrl = '" . View::url('payment/return?ref=' . urlencode($trx['trx_reference'])) . "';

    // Click handler for gateway selector item
    $('.gateway-selector-item').on('click', function() {
        $('.gateway-selector-item').removeClass('selected');
        $(this).addClass('selected');
        const radio = $(this).find('input[type=radio]');
        radio.prop('checked', true);
        currentGateway = radio.val();
    });

    $('input[name=gateway_choice]').on('change', function() {
        currentGateway = $(this).val();
        $('.gateway-selector-item').removeClass('selected');
        $(this).closest('.gateway-selector-item').addClass('selected');
    });

    $('#btn-pay-now').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<span class=\"spinner-border spinner-border-sm me-2\"></span>Memproses...');

        // Step 1: Initiate or fetch payment token for selected gateway
        $.ajax({
            url: '" . View::url('payment/initiate') . "',
            type: 'POST',
            data: {
                ref: trxReference,
                gateway: currentGateway
            },
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class=\"bi bi-shield-lock-fill me-1\"></i> Bayar Sekarang');

                if (!res.success) {
                    alert('Gagal memulai pembayaran: ' + (res.message || 'Kesalahan sistem'));
                    return;
                }

                if (currentGateway === 'midtrans') {
                    const token = res.snap_token || snapToken;
                    if (window.snap && token) {
                        window.snap.pay(token, {
                            onSuccess: function(result) {
                                window.location.href = finishUrl;
                            },
                            onPending: function(result) {
                                window.location.href = finishUrl;
                            },
                            onError: function(result) {
                                alert('Pembayaran gagal atau dibatalkan.');
                            },
                            onClose: function() {
                                console.log('Snap popup closed without payment.');
                            }
                        });
                    } else if (res.payment_url) {
                        window.location.href = res.payment_url;
                    }
                } else if (currentGateway === 'duitku') {
                    const reference = res.reference || duitkuReference;
                    const isSandbox = (res.mode === 'sandbox') || (res.payment_url && res.payment_url.includes('app-sandbox'));

                    // On Duitku Sandbox, X-Frame-Options: SAMEORIGIN blocks iframe embeds, so redirect is required
                    if (isSandbox && res.payment_url) {
                        window.location.href = res.payment_url;
                    } else if (window.checkout && reference) {
                        try {
                            checkout.process(reference, {
                                defaultLanguage: 'id',
                                successEvent: function(result) {
                                    window.location.href = finishUrl;
                                },
                                pendingEvent: function(result) {
                                    window.location.href = finishUrl;
                                },
                                errorEvent: function(result) {
                                    if (res.payment_url) {
                                        window.location.href = res.payment_url;
                                    }
                                },
                                closeEvent: function() {
                                    console.log('Duitku popup closed.');
                                }
                            });
                        } catch (err) {
                            console.warn('Checkout process error, fallback to URL:', err);
                            if (res.payment_url) {
                                window.location.href = res.payment_url;
                            }
                        }
                    } else if (res.payment_url) {
                        window.location.href = res.payment_url;
                    }
                } else if (currentGateway === 'doku') {
                    if (res.payment_url) {
                        window.location.href = res.payment_url;
                    }
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class=\"bi bi-shield-lock-fill me-1\"></i> Bayar Sekarang');
                alert('Terjadi kesalahan jaringan saat memproses pembayaran.');
            }
        });
    });
});
</script>
";
?>

