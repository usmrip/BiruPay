<?php
use App\Core\View;
?>

<div class="row g-4">
    <!-- Main Settings Form -->
    <div class="col-lg-8">
        <form action="<?= View::url('settings') ?>" method="POST">
            <?= View::csrfField() ?>

            <!-- Telegram Notification Settings -->
            <div class="card-custom">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded bg-info-subtle text-info"><i class="bi bi-telegram fs-5"></i></span>
                        <div>
                            <h6 class="mb-0 fw-bold">Notifikasi Telegram</h6>
                            <small class="text-muted">Kirim laporan notifikasi pembayaran real-time ke Telegram bot / channel</small>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="telegram_enabled" id="telegram_enabled" value="1" <?= ($settings['telegram_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="telegram_enabled">Aktifkan</label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Telegram Bot Token</label>
                        <input type="text" name="telegram_bot_token" class="form-control font-monospace" value="<?= View::escape($settings['telegram_bot_token'] ?? '') ?>" placeholder="123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ">
                        <small class="text-muted">Didapatkan dari @BotFather di aplikasi Telegram</small>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Telegram Chat ID / Group ID / Channel ID</label>
                        <input type="text" name="telegram_chat_id" class="form-control font-monospace" value="<?= View::escape($settings['telegram_chat_id'] ?? '') ?>" placeholder="Contoh: -100123456789 atau ID personal">
                        <small class="text-muted">Gunakan Chat ID grup, channel, atau akun pribadi penerima notifikasi</small>
                    </div>
                </div>
            </div>

            <!-- Email Notification Settings -->
            <div class="card-custom">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-2 rounded bg-primary-subtle text-primary"><i class="bi bi-envelope-at fs-5"></i></span>
                        <div>
                            <h6 class="mb-0 fw-bold">Notifikasi Email (SMTP)</h6>
                            <small class="text-muted">Kirim bukti pembayaran ke pelanggan dan notifikasi ke tim finance</small>
                        </div>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" name="email_enabled" id="email_enabled" value="1" <?= ($settings['email_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label fw-semibold" for="email_enabled">Aktifkan</label>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Mail Driver</label>
                            <select name="mail_driver" class="form-select">
                                <option value="smtp" <?= ($settings['mail_driver'] ?? 'smtp') === 'smtp' ? 'selected' : '' ?>>SMTP (Rekomendasi)</option>
                                <option value="mail" <?= ($settings['mail_driver'] ?? '') === 'mail' ? 'selected' : '' ?>>PHP mail()</option>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">SMTP Host</label>
                            <input type="text" name="mail_host" class="form-control font-monospace" value="<?= View::escape($settings['mail_host'] ?? 'mail.biru.co.id') ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Port & Enkripsi</label>
                            <div class="input-group">
                                <input type="number" name="mail_port" class="form-control" value="<?= View::escape($settings['mail_port'] ?? '587') ?>">
                                <select name="mail_encryption" class="form-select" style="max-width: 90px;">
                                    <option value="tls" <?= ($settings['mail_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS</option>
                                    <option value="ssl" <?= ($settings['mail_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                                    <option value="none" <?= ($settings['mail_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">SMTP Username</label>
                            <input type="text" name="mail_username" class="form-control" value="<?= View::escape($settings['mail_username'] ?? '') ?>" placeholder="user@biru.co.id">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">SMTP Password</label>
                            <div class="input-group">
                                <input type="password" name="mail_password" class="form-control" value="<?= View::escape($settings['mail_password'] ?? '') ?>" placeholder="••••••••">
                                <button class="btn btn-outline-secondary toggle-password" type="button"><i class="bi bi-eye"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email Pengirim (From Address)</label>
                            <input type="email" name="mail_from_address" class="form-control" value="<?= View::escape($settings['mail_from_address'] ?? 'no-reply@biru.co.id') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Pengirim (From Name)</label>
                            <input type="text" name="mail_from_name" class="form-control" value="<?= View::escape($settings['mail_from_name'] ?? 'Biru Payment Hub') ?>">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Email Administrator Penerima Notifikasi Transaksi</label>
                        <input type="email" name="admin_notification_email" class="form-control" value="<?= View::escape($settings['admin_notification_email'] ?? 'finance@biru.co.id') ?>">
                        <small class="text-muted">Setiap pembayaran sukses akan dikirimkan salinannya ke email ini</small>
                    </div>
                </div>
            </div>

            <!-- General Settings -->
            <div class="card-custom">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-sliders me-1 text-primary"></i> Identitas Sistem</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Aplikasi</label>
                            <input type="text" name="app_name" class="form-control" value="<?= View::escape($settings['app_name'] ?? 'Biru PayHub') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Nama Perusahaan / Organisasi</label>
                            <input type="text" name="company_name" class="form-control" value="<?= View::escape($settings['company_name'] ?? 'Biru Indonesia Creative') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-end mb-4">
                <button type="submit" class="btn btn-primary px-4 py-2 fw-semibold">
                    <i class="bi bi-save me-1"></i> Simpan Semua Pengaturan
                </button>
            </div>
        </form>
    </div>

    <!-- Testing Box (Side Column) -->
    <div class="col-lg-4">
        <!-- Test Telegram -->
        <div class="card-custom mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold"><i class="bi bi-send me-1 text-info"></i> Tes Pengiriman Telegram</h6>
            </div>
            <div class="card-body">
                <form action="<?= View::url('settings/test-telegram') ?>" method="POST">
                    <?= View::csrfField() ?>
                    <p class="small text-muted mb-3">Kirimkan pesan uji coba ke bot Telegram untuk memastikan token & chat ID bekerja:</p>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Bot Token</label>
                        <input type="text" name="telegram_bot_token" class="form-control form-control-sm font-monospace" value="<?= View::escape($settings['telegram_bot_token'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Chat ID Tujuan</label>
                        <input type="text" name="telegram_chat_id" class="form-control form-control-sm font-monospace" value="<?= View::escape($settings['telegram_chat_id'] ?? '') ?>" required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-info text-white w-100">
                        <i class="bi bi-send me-1"></i> Kirim Pesan Tes Telegram
                    </button>
                </form>
            </div>
        </div>

        <!-- Test Email -->
        <div class="card-custom">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold"><i class="bi bi-envelope-check me-1 text-primary"></i> Tes Pengiriman Email</h6>
            </div>
            <div class="card-body">
                <form action="<?= View::url('settings/test-email') ?>" method="POST">
                    <?= View::csrfField() ?>
                    <p class="small text-muted mb-3">Kirimkan email uji coba untuk memverifikasi pengaturan SMTP server:</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Penerima Tes</label>
                        <input type="email" name="test_email" class="form-control form-control-sm" placeholder="anda@biru.co.id" value="<?= View::escape($settings['admin_notification_email'] ?? '') ?>" required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100">
                        <i class="bi bi-send me-1"></i> Kirim Email Tes
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

