<?php
use App\Core\View;
$isEdit = !empty($user);
$action = $isEdit ? View::url('users/' . $user['id']) : View::url('users');
?>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card-custom">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold"><?= $isEdit ? 'Edit User' : 'Tambah User Baru' ?></h6>
            </div>
            <div class="card-body">
                <form action="<?= $action ?>" method="POST">
                    <?= View::csrfField() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="<?= View::escape($user['name'] ?? '') ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Username</label>
                            <input type="text" name="username" class="form-control" value="<?= View::escape($user['username'] ?? '') ?>" <?= $isEdit ? 'readonly' : 'required' ?>>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= View::escape($user['email'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password <?= $isEdit ? '(Biarkan kosong jika tidak diubah)' : '' ?></label>
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" placeholder="••••••••" <?= $isEdit ? '' : 'required' ?>>
                            <button class="btn btn-outline-secondary toggle-password" type="button"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Role Hak Akses</label>
                            <select name="role" class="form-select">
                                <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin (Akses Penuh)</option>
                                <option value="operator" <?= ($user['role'] ?? '') === 'operator' ? 'selected' : '' ?>>Operator (Lihat Laporan)</option>
                            </select>
                        </div>
                        <?php if ($isEdit): ?>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold">Status Akun</label>
                                <select name="status" class="form-select">
                                    <option value="active" <?= ($user['status'] ?? '') === 'active' ? 'selected' : '' ?>>Aktif</option>
                                    <option value="inactive" <?= ($user['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="<?= View::url('users') ?>" class="btn btn-light border">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> <?= $isEdit ? 'Perbarui User' : 'Simpan User' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

