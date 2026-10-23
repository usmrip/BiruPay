<?php
use App\Core\View;
use App\Core\Session;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::escape($pageTitle ?? 'Autentikasi') ?> - Biru PayHub</title>
    <link rel="stylesheet" href="<?= View::asset('css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= View::asset('css/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= View::asset('css/app.css') ?>">
</head>
<body class="d-flex align-items-center min-vh-100 py-5">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mb-4">
                <div class="brand-logo mx-auto mb-2" style="width: 50px; height: 50px; background: linear-gradient(135deg, #3b82f6, #1d4ed8); border-radius: 14px; box-shadow: 0 8px 18px -8px rgba(37,99,235,.55); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 1.5rem;">B</div>
                <h4 class="fw-bold mb-0 text-dark">Biru PayHub</h4>
                <p class="text-muted small">Sistem Pembayaran Terpusat - Biru Indonesia Creative</p>
            </div>

            <!-- Flash alerts -->
            <?php foreach (['success', 'danger', 'warning', 'info'] as $type): ?>
                <?php if (Session::hasFlash($type)): ?>
                    <?php foreach (Session::getFlash($type) as $msg): ?>
                        <div class="alert alert-<?= $type ?> alert-dismissible fade show small" role="alert">
                            <?= $msg ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <?= $content ?>

            <div class="text-center mt-4 text-muted small">
                &copy; <?= date('Y') ?> Biru Indonesia Creative
            </div>
        </div>
    </div>
</div>

<script src="<?= View::asset('js/jquery.min.js') ?>"></script>
<script src="<?= View::asset('js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= View::asset('js/app.js') ?>"></script>
</body>
</html>

