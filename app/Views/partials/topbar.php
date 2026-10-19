<?php
/**
 * Partial: navbar atas (admin) + dropdown user.
 * Variabel: $title (judul halaman)
 */
helper('kost');
$user = session()->get('user') ?? [];
?>
<div class="kosku-topbar sticky-top px-3 py-2">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-light d-none d-lg-inline-flex" type="button" id="btnToggleSidebar"
                aria-controls="sidebarAdmin" aria-expanded="true"
                title="Ringkas menu" aria-label="Ringkas menu">
            <i class="bi bi-chevron-double-left ikon-rapatkan"></i>
            <i class="bi bi-chevron-double-right ikon-bentangkan"></i>
        </button>
        <button class="btn btn-light d-lg-none" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#sidebarKosku" aria-controls="sidebarKosku"
                aria-label="Buka menu">
            <i class="bi bi-list"></i>
        </button>
        <h1 class="h5 mb-0 flex-grow-1 text-truncate"><?= esc($title ?? 'Dashboard') ?></h1>

        <div class="dropdown">
            <button class="btn btn-light d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="avatar-initial"><?= esc(inisial_nama($user['name'] ?? 'A')) ?></span>
                <span class="d-none d-md-inline small text-start lh-sm">
                    <span class="d-block fw-semibold"><?= esc($user['name'] ?? 'Admin') ?></span>
                    <span class="d-block text-secondary"><?= esc(ucfirst($user['role'] ?? 'admin')) ?></span>
                </span>
                <i class="bi bi-chevron-down small"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><a class="dropdown-item" href="<?= base_url('profil') ?>"><i class="bi bi-person me-2"></i>Profil Saya</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
            </ul>
        </div>
    </div>
</div>
