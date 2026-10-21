<?php
use App\Core\View;
?>
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-2 text-dark text-center">Konfigurasi Database</h5>
        <p class="text-muted small text-center mb-4">Sistem membutuhkan koneksi ke database MySQL untuk menyimpan data pembayaran dan platform.</p>

        <?php if (!empty($error)): ?>
            <div class="alert alert-warning small mb-3">
                <i class="bi bi-info-circle me-1"></i> <?= View::escape($error) ?>
            </div>
        <?php endif; ?>

        <form action="<?= View::url('install') ?>" method="POST">
            <?= View::csrfField() ?>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Host Database</label>
                <input type="text" name="db_host" class="form-control" value="127.0.0.1" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Port Database</label>
                <input type="number" name="db_port" class="form-control" value="3306" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Nama Database</label>
                <input type="text" name="db_name" class="form-control" value="biru_pembayaran" required>
                <small class="text-muted">Pastikan database sudah dibuat di cPanel / phpMyAdmin.</small>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Username Database</label>
                <input type="text" name="db_user" class="form-control" value="root" required>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold text-secondary">Password Database</label>
                <input type="password" name="db_pass" class="form-control" placeholder="Kosongkan jika tanpa password">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3" style="background-color: #0052cc; border-color: #0052cc;">
                <i class="bi bi-database-check me-1"></i> Simpan & Pasang Database
            </button>
        </form>
    </div>
</div>

