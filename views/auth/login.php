<?php
use App\Core\View;
?>
<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3 text-dark text-center">Masuk ke Dashboard</h5>
        <form action="<?= View::url('login') ?>" method="POST">
            <?= View::csrfField() ?>

            <div class="mb-3">
                <label for="username" class="form-label small fw-semibold text-secondary">Username atau Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                    <input type="text" class="form-control border-start-0" id="username" name="username" placeholder="Masukkan username" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label for="password" class="form-label small fw-semibold text-secondary mb-0">Password</label>
                </div>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                    <input type="password" class="form-control border-start-0 border-end-0" id="password" name="password" placeholder="••••••••" required>
                    <button class="btn btn-light border border-start-0 toggle-password" type="button">
                        <i class="bi bi-eye text-muted"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3" style="background-color: #0052cc; border-color: #0052cc;">
                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
            </button>
        </form>
    </div>
</div>

