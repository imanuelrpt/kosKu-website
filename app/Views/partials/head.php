<?php
/**
 * Partial: tag <head> berisi CDN Bootstrap, ikon, dan CSS global KosKu.
 * Variabel opsional: $title
 */
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title ?? 'Dashboard') ?> &middot; KosKu</title>
<meta name="csrf-token" content="<?= csrf_hash() ?>">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

<style>
    :root {
        --kosku-primary: #0d6efd;
        --kosku-sidebar-bg: #16233a;
        --kosku-sidebar-hover: #22314d;
        --kosku-sidebar-active: #0d6efd;
        --kosku-sidebar-width: 250px;
        --kosku-sidebar-width-mini: 76px;
    }

    body {
        min-height: 100vh;
        background-color: #f4f6fb;
    }

    /* ---------- Sidebar (admin) ---------- */
    .kosku-sidebar {
        width: var(--kosku-sidebar-width);
        min-height: 100vh;
        background: var(--kosku-sidebar-bg);
        color: #cbd5e1;
        flex-shrink: 0;
    }
    .kosku-sidebar .sidebar-brand {
        color: #fff;
        font-weight: 700;
        font-size: 1.25rem;
        letter-spacing: .3px;
    }
    .kosku-sidebar .sidebar-brand small {
        display: block;
        font-size: .68rem;
        font-weight: 400;
        color: #94a3b8;
        letter-spacing: 1.5px;
        text-transform: uppercase;
    }
    .kosku-sidebar .nav-link {
        color: #cbd5e1;
        border-radius: .5rem;
        padding: .6rem .85rem;
        margin-bottom: .15rem;
        display: flex;
        align-items: center;
        gap: .65rem;
        font-size: .94rem;
    }
    .kosku-sidebar .nav-link i {
        font-size: 1.05rem;
        width: 1.25rem;
        text-align: center;
    }
    .kosku-sidebar .nav-link:hover {
        background: var(--kosku-sidebar-hover);
        color: #fff;
    }
    .kosku-sidebar .nav-link.active {
        background: var(--kosku-sidebar-active);
        color: #fff;
        font-weight: 600;
    }
    .kosku-sidebar .sidebar-section {
        font-size: .7rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        margin: 1rem .85rem .4rem;
    }
    .kosku-sidebar.offcanvas-lg {
        background: var(--kosku-sidebar-bg);
        border-right: 0;
    }

    /* Sidebar desktop bisa diringkas jadi rail ikon (mini sidebar) */
    .kosku-sidebar .sidebar-inner {
        padding: 1rem;
    }
    .kosku-sidebar-desktop {
        height: 100vh;
        transition: width .18s ease;
        overflow-x: hidden;
        overflow-y: auto;
        scrollbar-width: none;      /* Firefox */
        -ms-overflow-style: none;   /* Edge versi lama */
    }
    .kosku-sidebar-desktop::-webkit-scrollbar { width: 0; height: 0; }
    /* Menu geser (mobile) juga tanpa scrollbar */
    .kosku-sidebar.offcanvas .offcanvas-body {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .kosku-sidebar.offcanvas .offcanvas-body::-webkit-scrollbar { width: 0; height: 0; }
    body.sidebar-mini .kosku-sidebar-desktop {
        width: var(--kosku-sidebar-width-mini);
    }
    body.sidebar-mini .kosku-sidebar-desktop .sidebar-inner {
        padding-left: .5rem;
        padding-right: .5rem;
    }
    body.sidebar-mini .kosku-sidebar-desktop .sidebar-brand {
        justify-content: center;
    }
    body.sidebar-mini .kosku-sidebar-desktop .nav-link {
        justify-content: center;
        padding-left: .25rem;
        padding-right: .25rem;
    }
    body.sidebar-mini .kosku-sidebar-desktop .nav-link span,
    body.sidebar-mini .kosku-sidebar-desktop .sidebar-brand span,
    body.sidebar-mini .kosku-sidebar-desktop .sidebar-foot {
        display: none;
    }
    @media (prefers-reduced-motion: reduce) {
        .kosku-sidebar-desktop { transition: none; }
    }

    /* Tombol buka/tutup sidebar */
    #btnToggleSidebar .ikon-bentangkan { display: none; }
    body.sidebar-mini #btnToggleSidebar .ikon-rapatkan { display: none; }
    body.sidebar-mini #btnToggleSidebar .ikon-bentangkan { display: inline-block; }

    /* Konten utama */
    .kosku-main { min-width: 0; }
    .kosku-topbar {
        background: #fff;
        border-bottom: 1px solid #e5e9f2;
    }

    /* ---------- Navbar (penghuni) ---------- */
    .kosku-tenant-nav {
        background: var(--kosku-sidebar-bg);
    }
    .kosku-tenant-nav .navbar-brand,
    .kosku-tenant-nav .nav-link {
        color: #e2e8f0;
    }
    .kosku-tenant-nav .nav-link:hover,
    .kosku-tenant-nav .nav-link.active {
        color: #fff;
    }

    /* ---------- Umum ---------- */
    .stat-card { border: 0; border-radius: .85rem; }
    .stat-card .stat-icon {
        width: 48px; height: 48px;
        border-radius: .75rem;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.35rem;
    }
    .table > :not(caption) > * > * { vertical-align: middle; }
    .avatar-initial {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: #e7f0ff;
        color: var(--kosku-primary);
        display: inline-flex; align-items: center; justify-content: center;
        font-weight: 600; font-size: .85rem;
    }
    .required::after { content: " *"; color: #dc3545; }

    @media (max-width: 575.98px) {
        .table-responsive-sm-card { font-size: .875rem; }
    }

    /* ---------- Print (kwitansi) ---------- */
    @media print {
        .no-print, .kosku-sidebar, .kosku-topbar, .kosku-tenant-nav { display: none !important; }
        body { background: #fff !important; }
        .print-area { box-shadow: none !important; border: 0 !important; }
    }
</style>
