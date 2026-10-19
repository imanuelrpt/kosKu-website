<?php
/**
 * Halaman "Cek Sistem" (Tahap 1) - memastikan environment siap pakai.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/head', ['title' => 'Cek Sistem']) ?>
    <style>
        body { background: linear-gradient(135deg, #16233a 0%, #0d6efd 100%); }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">

            <div class="text-center text-white mb-4">
                <i class="bi bi-house-heart-fill" style="font-size:3.5rem"></i>
                <h1 class="h2 mt-2 mb-1 fw-bold">KosKu</h1>
                <p class="mb-0 opacity-75">Sistem Manajemen Kos-kosan &middot; Tahap 3: Autentikasi &amp; Peran</p>
            </div>

            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-body p-4">

                    <h2 class="h5 mb-3"><i class="bi bi-cpu me-2"></i>Informasi Lingkungan</h2>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-4">
                            <tbody>
                            <tr>
                                <th style="width:40%">Versi PHP</th>
                                <td><code><?= esc($phpVersion) ?></code></td>
                            </tr>
                            <tr>
                                <th>Versi CodeIgniter</th>
                                <td><code><?= esc($ciVersion) ?></code></td>
                            </tr>
                            <tr>
                                <th>Environment</th>
                                <td><code><?= esc($environment) ?></code></td>
                            </tr>
                            <tr>
                                <th>Timezone</th>
                                <td><code><?= esc($timezone) ?></code></td>
                            </tr>
                            <tr>
                                <th>Base URL</th>
                                <td><code><?= esc(base_url()) ?></code></td>
                            </tr>
                            <tr>
                                <th>Database</th>
                                <td>
                                    <?php if ($db['ok']): ?>
                                        <span class="badge text-bg-success"><i class="bi bi-check-lg"></i> Terhubung</span>
                                        <code class="ms-1"><?= esc($db['nama']) ?></code>
                                        <span class="text-secondary small">(MySQL <?= esc($db['versi']) ?>)</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-danger"><i class="bi bi-x-lg"></i> Gagal</span>
                                        <div class="text-danger small mt-1"><?= esc($db['pesan']) ?></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>

                    <h2 class="h5 mb-3"><i class="bi bi-database-check me-2"></i>Data Demo</h2>
                    <p class="text-secondary small mb-3">
                        Hasil <code>php spark migrate</code> + <code>php spark db:seed DatabaseSeeder</code>.
                        Angka <span class="text-danger">-</span> berarti tabel belum ada.
                    </p>
                    <?php if ($ringkas === []): ?>
                        <div class="alert alert-warning mb-4">
                            <i class="bi bi-exclamation-triangle me-1"></i>Tidak dapat membaca data. Periksa koneksi database.
                        </div>
                    <?php else: ?>
                        <div class="row g-2 mb-4">
                            <?php
                            $labelTabel = [
                                'users'       => ['Akun', 'people'],
                                'rooms'       => ['Kamar', 'door-open'],
                                'tenants'     => ['Penghuni', 'person-vcard'],
                                'occupancies' => ['Sewa', 'key'],
                                'invoices'    => ['Tagihan', 'receipt'],
                                'payments'    => ['Pembayaran', 'cash-coin'],
                                'expenses'    => ['Pengeluaran', 'wallet2'],
                            ];
                            ?>
                            <?php foreach ($ringkas as $tabel => $jumlah): ?>
                                <div class="col-6 col-md-3 col-xl-3">
                                    <div class="border rounded-3 p-2 px-3 h-100 bg-body-tertiary">
                                        <div class="text-secondary small">
                                            <i class="bi bi-<?= $labelTabel[$tabel][1] ?> me-1"></i><?= esc($labelTabel[$tabel][0]) ?>
                                        </div>
                                        <div class="fs-5 fw-bold">
                                            <?= $jumlah === null ? '<span class="text-danger">-</span>' : esc($jumlah) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <h2 class="h5 mb-3"><i class="bi bi-puzzle me-2"></i>Ekstensi PHP</h2>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <?php foreach ($ekstensi as $nama => $ada): ?>
                            <span class="badge <?= $ada ? 'text-bg-success' : 'text-bg-danger' ?> fs-6 fw-normal px-3 py-2">
                                <i class="bi bi-<?= $ada ? 'check-circle-fill' : 'x-circle-fill' ?> me-1"></i><?= esc($nama) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <h2 class="h5 mb-3"><i class="bi bi-door-open me-2"></i>Masuk Aplikasi</h2>
                    <p class="text-secondary small mb-3">
                        Akun demo &mdash; admin: <code>admin@kosku.test</code> / <code>admin123</code>,
                        penghuni: <code>siti@kosku.test</code> / <code>penghuni123</code>
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <a href="<?= base_url('login') ?>" class="btn btn-primary">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
                        </a>
                        <a href="<?= base_url('register') ?>" class="btn btn-outline-primary">
                            <i class="bi bi-person-plus me-1"></i>Daftar Penghuni
                        </a>
                    </div>

                    <h2 class="h5 mb-3"><i class="bi bi-easel me-2"></i>Preview Layout</h2>
                    <p class="text-secondary small mb-3">
                        Tautan berikut hanya aktif pada environment <code>development</code>, untuk memeriksa
                        tampilan layout sebelum fitur dibangun.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="<?= base_url('preview/admin') ?>" class="btn btn-outline-primary">
                            <i class="bi bi-layout-sidebar me-1"></i>Layout Admin
                        </a>
                        <a href="<?= base_url('preview/tenant') ?>" class="btn btn-outline-primary">
                            <i class="bi bi-person-badge me-1"></i>Layout Penghuni
                        </a>
                        <a href="<?= base_url('preview/auth') ?>" class="btn btn-outline-primary">
                            <i class="bi bi-shield-lock me-1"></i>Layout Login
                        </a>
                    </div>
                </div>
            </div>

            <p class="text-center text-white-50 small mt-4 mb-0">
                Selanjutnya (Tahap 4): CRUD Kamar &amp; Penghuni.
            </p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
