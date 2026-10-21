<?php
use App\Core\View;
use App\Core\Session;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h5 class="fw-bold mb-0">User Manager</h5>
        <small class="text-muted">Kelola akun administrator dan operator sistem pembayaran</small>
    </div>
    <a href="<?= View::url('users/create') ?>" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i> Tambah User Baru
    </a>
</div>

<div class="card-custom">
    <div class="table-responsive">
        <table class="table table-custom table-hover mb-0">
            <thead>
                <tr>
                    <th>Nama User</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Waktu Dibuat</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                                </div>
                                <div class="fw-bold text-dark"><?= View::escape($user['name']) ?></div>
                            </div>
                        </td>
                        <td class="font-monospace small"><?= View::escape($user['username']) ?></td>
                        <td><?= View::escape($user['email']) ?></td>
                        <td>
                            <span class="badge <?= $user['role'] === 'admin' ? 'bg-primary' : 'bg-info' ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge <?= $user['status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>">
                                <?= ucfirst($user['status']) ?>
                            </span>
                        </td>
                        <td class="small text-muted"><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= View::url('users/' . $user['id'] . '/edit') ?>" class="btn btn-light border" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <?php if ((int)$user['id'] !== (int)Session::get('user_id')): ?>
                                    <form action="<?= View::url('users/' . $user['id'] . '/delete') ?>" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                        <?= View::csrfField() ?>
                                        <button type="submit" class="btn btn-light border text-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

