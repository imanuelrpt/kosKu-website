<?php
/**
 * Partial: sidebar navigasi admin.
 * Menandai menu aktif berdasarkan segmen URL pertama.
 */
helper('kost');

$segmen = service('request')->getUri()->getSegment(1);

/** @var array<int, array<string, mixed>> $menu */
$menu = [
    ['segmen' => 'dashboard',   'label' => 'Dashboard',      'icon' => 'speedometer2',   'url' => 'dashboard'],
    ['segmen' => 'kamar',       'label' => 'Kamar',          'icon' => 'door-open',      'url' => 'kamar'],
    ['segmen' => 'penghuni',    'label' => 'Penghuni',       'icon' => 'people',         'url' => 'penghuni'],
    ['segmen' => 'sewa',        'label' => 'Sewa / Check-in', 'icon' => 'key',           'url' => 'sewa'],
    ['segmen' => 'tagihan',     'label' => 'Tagihan',        'icon' => 'receipt',        'url' => 'tagihan'],
    ['segmen' => 'pembayaran',  'label' => 'Pembayaran',     'icon' => 'cash-coin',      'url' => 'pembayaran'],
    ['segmen' => 'pengeluaran', 'label' => 'Pengeluaran',    'icon' => 'wallet2',        'url' => 'pengeluaran'],
    ['segmen' => 'laporan',     'label' => 'Laporan',        'icon' => 'bar-chart-line', 'url' => 'laporan'],
    ['segmen' => 'pengaturan',  'label' => 'Pengaturan',     'icon' => 'sliders',        'url' => 'pengaturan'],
];
?>
<div class="sidebar-inner d-flex flex-column h-100">
    <a href="<?= base_url('dashboard') ?>" class="sidebar-brand text-decoration-none mb-4 px-2 d-flex align-items-center gap-2"
       data-judul-mini="KosKu">
        <i class="bi bi-house-heart-fill fs-3 text-primary"></i>
        <span>KosKu<small>Manajemen Kos</small></span>
    </a>

    <ul class="nav nav-pills flex-column mb-auto">
        <?php foreach ($menu as $item): ?>
            <li class="nav-item">
                <a href="<?= base_url($item['url']) ?>"
                   class="nav-link <?= $segmen === $item['segmen'] ? 'active' : '' ?>"
                   data-judul-mini="<?= esc($item['label'], 'attr') ?>">
                    <i class="bi bi-<?= $item['icon'] ?>"></i>
                    <span><?= esc($item['label']) ?></span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>

    <hr class="border-secondary-subtle">
    <ul class="nav nav-pills flex-column mb-2">
        <li class="nav-item">
            <a href="<?= base_url('profil') ?>" class="nav-link <?= $segmen === 'profil' ? 'active' : '' ?>"
               data-judul-mini="Profil Saya">
                <i class="bi bi-person-gear"></i><span>Profil Saya</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="<?= base_url('logout') ?>" class="nav-link text-danger-emphasis" data-judul-mini="Keluar">
                <i class="bi bi-box-arrow-right"></i><span>Keluar</span>
            </a>
        </li>
    </ul>
    <div class="sidebar-foot small text-secondary px-2 pt-2 border-top border-secondary-subtle">
        &copy; <?= date('Y') ?> KosKu
    </div>
</div>
