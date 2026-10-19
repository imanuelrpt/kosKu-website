<?php
/**
 * Layout portal penghuni: navbar sederhana + konten.
 * View halaman memakai:  $this->extend('layouts/tenant');
 */
helper('kost');
$user = session()->get('user') ?? [];

// Menu aktif ditentukan dari segmen URL (mis. "penghuni/beranda").
$segmen = trim(service('request')->getUri()->getSegment(1) . '/' . service('request')->getUri()->getSegment(2), '/');

$menuPenghuni = [
    ['segmen' => 'penghuni/beranda', 'label' => 'Dashboard',    'icon' => 'speedometer2', 'url' => 'penghuni/beranda'],
    ['segmen' => 'penghuni/tagihan', 'label' => 'Tagihan Saya', 'icon' => 'receipt',      'url' => 'penghuni/tagihan'],
    ['segmen' => 'penghuni/profil',  'label' => 'Profil',       'icon' => 'person-gear',  'url' => 'penghuni/profil'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/head', ['title' => $title ?? 'Portal Penghuni']) ?>
    <?= $this->renderSection('styles') ?>
</head>
<body>
<nav class="navbar navbar-expand-lg kosku-tenant-nav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="<?= base_url('penghuni/beranda') ?>">
            <i class="bi bi-house-heart-fill fs-4"></i> KosKu
        </a>
        <button class="navbar-toggler border-light" type="button" data-bs-toggle="collapse" data-bs-target="#navPenghuni" aria-controls="navPenghuni" aria-expanded="false" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navPenghuni">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <?php foreach ($menuPenghuni as $item): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $segmen === $item['segmen'] ? 'active' : '' ?>" href="<?= base_url($item['url']) ?>">
                            <i class="bi bi-<?= $item['icon'] ?> me-1"></i><?= esc($item['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <span class="text-white-50 small d-none d-lg-inline">Halo, <?= esc($user['name'] ?? 'Penghuni') ?></span>
                <a class="btn btn-sm btn-outline-light" href="<?= base_url('logout') ?>">
                    <i class="bi bi-box-arrow-right me-1"></i>Keluar
                </a>
            </div>
        </div>
    </div>
</nav>

<main class="container py-4">
    <?= view('partials/flash') ?>
    <?= $this->renderSection('content') ?>
</main>

<footer class="text-center text-secondary small py-4 no-print">
    KosKu &middot; Sistem Manajemen Kos-kosan &copy; <?= date('Y') ?>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
