<?php
/**
 * Layout utama admin: sidebar + topbar + konten.
 * View halaman memakai:  $this->extend('layouts/admin');
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/head', ['title' => $title ?? 'Dashboard']) ?>
    <?= $this->renderSection('styles') ?>
</head>
<body>
<script>
    // Terapkan status ringkas sidebar sebelum halaman digambar agar tidak berkedip.
    (function () {
        try {
            if (localStorage.getItem('kosku-sidebar') === 'mini') {
                document.body.classList.add('sidebar-mini');
            }
        } catch (e) { /* localStorage bisa saja diblokir peramban */ }
    })();
</script>
<div class="d-flex">

    <!-- Sidebar untuk desktop -->
    <aside class="kosku-sidebar kosku-sidebar-desktop d-none d-lg-block position-sticky top-0" id="sidebarAdmin">
        <?= view('partials/sidebar') ?>
    </aside>

    <!-- Sidebar untuk mobile (offcanvas) -->
    <div class="offcanvas offcanvas-start kosku-sidebar" tabindex="-1" id="sidebarKosku" aria-labelledby="sidebarKoskuLabel">
        <div class="offcanvas-header pb-0">
            <span class="text-white fw-bold" id="sidebarKoskuLabel">Menu</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body p-0">
            <?= view('partials/sidebar') ?>
        </div>
    </div>

    <div class="kosku-main flex-grow-1 d-flex flex-column" style="min-height:100vh;">
        <?= view('partials/topbar', ['title' => $title ?? 'Dashboard']) ?>

        <main class="flex-grow-1 p-3 p-lg-4">
            <?= view('partials/flash') ?>
            <?= $this->renderSection('content') ?>
        </main>

        <footer class="text-center text-secondary small py-3 no-print">
            KosKu &middot; Sistem Manajemen Kos-kosan &copy; <?= date('Y') ?>
        </footer>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    // Buka/tutup sidebar admin (rail ikon); pilihan disimpan di localStorage.
    (function () {
        const tombol  = document.getElementById('btnToggleSidebar');
        const sidebar = document.getElementById('sidebarAdmin');
        if (! tombol || ! sidebar) { return; }

        const KUNCI   = 'kosku-sidebar';
        const sasaran = sidebar.querySelectorAll('[data-judul-mini]');

        function aturTooltip(mini) {
            sasaran.forEach(function (el) {
                const aktif = bootstrap.Tooltip.getInstance(el);
                if (mini && ! aktif) {
                    new bootstrap.Tooltip(el, {
                        title: el.getAttribute('data-judul-mini'),
                        placement: 'right',
                        container: 'body'
                    });
                } else if (! mini && aktif) {
                    aktif.dispose();
                }
            });
        }

        function terapkan(mini, simpan) {
            const sebelum = document.body.classList.contains('sidebar-mini');
            document.body.classList.toggle('sidebar-mini', mini);
            tombol.setAttribute('aria-expanded', mini ? 'false' : 'true');
            tombol.setAttribute('title', mini ? 'Tampilkan menu' : 'Ringkas menu');
            tombol.setAttribute('aria-label', tombol.getAttribute('title'));
            aturTooltip(mini);
            if (simpan && mini !== sebelum) {
                try { localStorage.setItem(KUNCI, mini ? 'mini' : 'penuh'); } catch (e) {}
            }
        }

        terapkan(document.body.classList.contains('sidebar-mini'), false);

        tombol.addEventListener('click', function () {
            terapkan(! document.body.classList.contains('sidebar-mini'), true);
        });
    })();
</script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
