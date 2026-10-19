<?php
/**
 * Layout halaman autentikasi (login/register): kartu terpusat.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/head', ['title' => $title ?? 'Masuk']) ?>
    <style>
        body {
            background: linear-gradient(135deg, #16233a 0%, #0d6efd 100%);
            display: flex;
            align-items: center;
            min-height: 100vh;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-11 col-sm-9 col-md-6 col-lg-5 col-xl-4">
            <div class="text-center text-white mb-4">
                <i class="bi bi-house-heart-fill" style="font-size:3rem"></i>
                <h1 class="h3 mt-2 mb-0 fw-bold">KosKu</h1>
                <p class="mb-0 opacity-75">Sistem Manajemen Kos-kosan</p>
            </div>

            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-4 p-md-4">
                    <?= view('partials/flash') ?>
                    <?= $this->renderSection('content') ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
