<?php
use App\Core\View;
use App\Core\Session;
$active = $activeNav ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::escape($pageTitle ?? 'Dashboard') ?> - Biru PayHub</title>
    <link rel="stylesheet" href="<?= View::asset('css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= View::asset('css/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= View::asset('css/app.css') ?>?v=<?= @filemtime(__DIR__ . '/../../public/assets/css/app.css') ?: 1 ?>">
</head>
<body>
<div class="d-flex">
    <div class="sidebar-backdrop" onclick="document.body.classList.remove('sidebar-open')"></div>
    <!-- Sidebar -->
    <aside class="app-sidebar d-flex flex-column">
        <a href="<?= View::url('dashboard') ?>" class="sidebar-brand">
            <div class="brand-logo">B</div>
            <div>
                <div>Biru PayHub</div>
                <small class="text-secondary fw-normal" style="font-size: 0.72rem;">Sistem Pembayaran Terpusat</small>
            </div>
        </a>

        <ul class="sidebar-menu flex-grow-1">
            <li class="menu-header">Menu Utama</li>
            <li class="menu-item">
                <a href="<?= View::url('dashboard') ?>" class="menu-link <?= $active === 'dashboard' ? 'active' : '' ?>">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= View::url('gateways') ?>" class="menu-link <?= $active === 'gateways' ? 'active' : '' ?>">
                    <i class="bi bi-credit-card-2-front"></i>
                    <span>Setup Gateway</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= View::url('applications') ?>" class="menu-link <?= $active === 'applications' ? 'active' : '' ?>">
                    <i class="bi bi-app-indicator"></i>
                    <span>Platform & Aplikasi</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= View::url('transactions') ?>" class="menu-link <?= $active === 'transactions' ? 'active' : '' ?>">
                    <i class="bi bi-receipt"></i>
                    <span>Laporan Transaksi</span>
                </a>
            </li>

            <li class="menu-header">Administrasi</li>
            <li class="menu-item">
                <a href="<?= View::url('users') ?>" class="menu-link <?= $active === 'users' ? 'active' : '' ?>">
                    <i class="bi bi-people"></i>
                    <span>User Manager</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= View::url('settings') ?>" class="menu-link <?= $active === 'settings' ? 'active' : '' ?>">
                    <i class="bi bi-gear"></i>
                    <span>Notifikasi & Setup</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="<?= View::url('docs') ?>" class="menu-link <?= $active === 'docs' ? 'active' : '' ?>">
                    <i class="bi bi-code-slash"></i>
                    <span>Panduan Integrasi</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="/demo/pembayaran" target="_blank" class="menu-link">
                    <i class="bi bi-play-circle"></i>
                    <span>Halaman Test Demo</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-user">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.85rem; font-weight: 700;">
                        <?= strtoupper(substr(Session::get('user_name', 'A'), 0, 1)) ?>
                    </div>
                    <div style="line-height: 1.2;">
                        <div class="small fw-semibold text-truncate" style="max-width: 130px;"><?= View::escape(Session::get('user_name', 'Admin')) ?></div>
                        <small class="text-secondary" style="font-size: 0.7rem;"><?= View::escape(Session::get('user_role', 'admin')) ?></small>
                    </div>
                </div>
                <a href="<?= View::url('logout') ?>" class="btn btn-sm btn-light border text-danger" title="Keluar">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="app-main">
        <header class="app-navbar d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="sidebar-toggle" aria-label="Menu" onclick="document.body.classList.toggle('sidebar-open')"><i class="bi bi-list"></i></button>
                <h5 class="mb-0 fw-bold text-dark"><?= View::escape($pageTitle ?? 'Dashboard') ?></h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-light text-secondary border px-3 py-2 d-none d-md-inline-flex">
                    <i class="bi bi-calendar3 me-1"></i> <?= date('d M Y') ?>
                </span>
                <a href="<?= View::url('docs') ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-terminal me-1"></i> <span class="d-none d-sm-inline">API Docs</span>
                </a>
            </div>
        </header>

        <main class="app-content">
            <!-- Flash Notifications -->
            <?php foreach (['success', 'danger', 'warning', 'info'] as $type): ?>
                <?php if (Session::hasFlash($type)): ?>
                    <?php foreach (Session::getFlash($type) as $msg): ?>
                        <div class="alert alert-<?= $type ?> alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm" role="alert">
                            <?php if ($type === 'success'): ?><i class="bi bi-check-circle-fill"></i><?php endif; ?>
                            <?php if ($type === 'danger'): ?><i class="bi bi-exclamation-triangle-fill"></i><?php endif; ?>
                            <?php if ($type === 'warning'): ?><i class="bi bi-exclamation-circle-fill"></i><?php endif; ?>
                            <?php if ($type === 'info'): ?><i class="bi bi-info-circle-fill"></i><?php endif; ?>
                            <div><?= $msg ?></div>
                            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <?= $content ?>
        </main>

        <footer class="text-center py-3 text-muted small border-top bg-white">
            &copy; <?= date('Y') ?> <strong>Biru Indonesia Creative</strong>. All rights reserved. &bull; Biru PayHub v1.0.2
        </footer>
    </div>
</div>

<script src="<?= View::asset('js/jquery.min.js') ?>"></script>
<script src="<?= View::asset('js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= View::asset('js/app.js') ?>"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>

