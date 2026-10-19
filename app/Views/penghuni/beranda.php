<?php
/**
 * Beranda portal penghuni.
 *
 * Variabel: $tenant, $sewa, $sewaTerakhir, $ringkasanTagihan, $ringkasanBayar,
 *           $berikutnya, $tagihanTerbaru, $pembayaranTerbaru, $durasi
 */
$this->extend('layouts/tenant');

$user      = user_login();
$kamar     = $sewa ?? $sewaTerakhir;
$aktif     = $sewa !== null;
$adaKamar  = $kamar !== null;

$waPengelola = setting('kos_phone')
    ? wa_link(setting('kos_phone'), 'Halo ' . setting('kos_name', 'KosKu') . ', saya '
        . ($tenant['name'] ?? 'penghuni') . ' kamar ' . ($kamar['room_number'] ?? '-')
        . ' ingin mengonfirmasi pembayaran tagihan saya.')
    : null;
?>

<?php if ($tenant === null): ?>
    <?php $this->section('content') ?>
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center p-5">
                    <span class="stat-icon text-bg-warning mx-auto mb-3" style="width:64px;height:64px;font-size:1.8rem;">
                        <i class="bi bi-person-plus"></i>
                    </span>
                    <h1 class="h4 mb-2">Akun Anda Belum Tertaut</h1>
                    <p class="text-secondary mb-4">
                        Akun <?= esc($user['email'] ?? 'Anda') ?> sudah aktif, tetapi belum ditautkan ke data penghuni
                        oleh pengelola kos. Setelah ditautkan, kamar, tagihan, dan riwayat pembayaran Anda akan tampil
                        di halaman ini.
                    </p>
                    <a href="<?= base_url('penghuni/profil') ?>" class="btn btn-primary">
                        <i class="bi bi-person-gear me-1"></i>Buka Profil Saya
                    </a>
                    <?php if (setting('kos_phone')): ?>
                        <a href="<?= esc(wa_link(setting('kos_phone'), 'Halo ' . setting('kos_name', 'KosKu') . ', akun saya (' . ($user['email'] ?? '') . ') belum tertaut ke data penghuni.'), 'attr') ?>"
                           target="_blank" rel="noopener" class="btn btn-success ms-2">
                            <i class="bi bi-whatsapp me-1"></i>Hubungi Pengelola
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php $this->endSection() ?>

<?php else: ?>

<?php $this->section('styles') ?>
<style>
    .hero-penghuni {
        background: linear-gradient(135deg, #16233a 0%, #24405f 55%, #0d6efd 165%);
        color: #fff;
        border-radius: 1rem;
    }
    .hero-penghuni .avatar-besar {
        width: 64px; height: 64px; font-size: 1.25rem;
        background: rgba(255, 255, 255, .18); color: #fff;
    }
    .hero-penghuni a.btn-outline-light:hover { color: #16233a; }
    .kartu-ringkas .list-group-item { background: transparent; }
</style>
<?php $this->endSection() ?>

<?php $this->section('content') ?>

<?php if ((int) $ringkasanTagihan['terlambat'] > 0): ?>
    <div class="alert alert-danger d-flex flex-wrap align-items-center gap-2 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5"></i>
        <div class="flex-grow-1">
            Anda memiliki <strong><?= (int) $ringkasanTagihan['terlambat'] ?> tagihan yang sudah lewat jatuh tempo</strong>.
            Mohon segera diselesaikan agar tidak menumpuk.
        </div>
        <a href="<?= base_url('penghuni/tagihan?status=terlambat') ?>" class="btn btn-sm btn-danger">
            Lihat Tagihan Terlambat
        </a>
    </div>
<?php endif; ?>

<div class="card hero-penghuni border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-wrap align-items-center gap-3">
            <span class="avatar-initial avatar-besar"><?= esc(inisial_nama($tenant['name'] ?? 'P')) ?></span>
            <div class="flex-grow-1">
                <div class="text-white-50 small">Selamat datang kembali,</div>
                <h1 class="h3 mb-1"><?= esc($tenant['name'] ?? 'Penghuni') ?></h1>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <?php if ($adaKamar): ?>
                        <span class="badge text-bg-light text-dark">
                            <i class="bi bi-door-open me-1"></i>Kamar <?= esc($kamar['room_number']) ?>
                        </span>
                        <span class="text-white-50">Lantai <?= (int) $kamar['floor'] ?></span>
                    <?php endif; ?>
                    <?php if ($adaKamar): ?>
                        <?= badge_sewa($aktif ? $sewa['status'] : ($kamar['status'] ?? 'selesai')) ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= base_url('penghuni/tagihan') ?>" class="btn btn-light btn-sm">
                    <i class="bi bi-receipt me-1"></i>Tagihan Saya
                </a>
                <?php if ($waPengelola !== null): ?>
                    <a href="<?= esc($waPengelola, 'attr') ?>" target="_blank" rel="noopener" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-whatsapp me-1"></i>Hubungi Pengelola
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-danger"><i class="bi bi-wallet2"></i></span>
                <div>
                    <div class="text-secondary small">Total Tunggakan</div>
                    <div class="fs-6 fw-bold lh-1 <?= $ringkasanTagihan['sisa'] > 0 ? 'text-danger' : 'text-success' ?>">
                        <?= rupiah($ringkasanTagihan['sisa']) ?>
                    </div>
                    <div class="small text-secondary">
                        <?= (int) $ringkasanTagihan['jumlah'] - (int) ($ringkasanTagihan['per_status']['lunas'] ?? 0) ?> tagihan belum lunas
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-success"><i class="bi bi-cash-coin"></i></span>
                <div>
                    <div class="text-secondary small">Total Sudah Dibayar</div>
                    <div class="fs-6 fw-bold lh-1 text-success"><?= rupiah($ringkasanBayar['total']) ?></div>
                    <div class="small text-secondary"><?= (int) $ringkasanBayar['jumlah'] ?> kali pembayaran</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-warning"><i class="bi bi-calendar-check"></i></span>
                <div>
                    <div class="text-secondary small">Jatuh Tempo Terdekat</div>
                    <div class="fs-6 fw-bold lh-1">
                        <?= $berikutnya !== null ? esc(tanggal_indo($berikutnya['due_date'])) : 'Tidak ada' ?>
                    </div>
                    <div class="small text-secondary">
                        <?= $berikutnya !== null
                            ? esc(periode_indo($berikutnya['period_month'], $berikutnya['period_year'])) . ' &middot; ' . rupiah($berikutnya['sisa'])
                            : 'Semua tagihan sudah lunas' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-primary"><i class="bi bi-hourglass-split"></i></span>
                <div>
                    <div class="text-secondary small">Lama Menempati</div>
                    <div class="fs-6 fw-bold lh-1"><?= esc($durasi['teks']) ?></div>
                    <div class="small text-secondary">
                        Sejak <?= $adaKamar ? esc(tanggal_indo($kamar['start_date'])) : 'belum ada kamar' ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Kolom kiri -->
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-receipt me-2"></i>Tagihan Terbaru</span>
                <a href="<?= base_url('penghuni/tagihan') ?>" class="btn btn-sm btn-outline-primary">
                    Lihat Semua<i class="bi bi-chevron-right ms-1"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Periode</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-end">Sisa</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($tagihanTerbaru === []): ?>
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-1"></i>Belum ada tagihan.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($tagihanTerbaru as $t): ?>
                        <tr>
                            <td class="text-nowrap fw-semibold">
                                <?= esc(periode_indo($t['period_month'], $t['period_year'])) ?>
                            </td>
                            <td class="text-end text-nowrap"><?= rupiah($t['amount']) ?></td>
                            <td class="text-end text-nowrap <?= $t['sisa'] > 0 ? 'text-danger fw-semibold' : 'text-secondary' ?>">
                                <?= rupiah($t['sisa']) ?>
                            </td>
                            <td class="text-nowrap"><?= esc(tanggal_indo($t['due_date'])) ?></td>
                            <td class="text-center"><?= badge_tagihan($t['status']) ?></td>
                            <td class="text-end text-nowrap">
                                <a href="<?= base_url('penghuni/tagihan/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary" title="Detail tagihan">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="<?= base_url('penghuni/tagihan/cetak/' . $t['id']) ?>" target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-secondary" title="Cetak / simpan PDF">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-clock-history me-2"></i>Pembayaran Terbaru</span>
                <span class="badge text-bg-secondary"><?= (int) $ringkasanBayar['jumlah'] ?> pembayaran</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Untuk Periode</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-center">Metode</th>
                            <th class="text-end">Kwitansi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($pembayaranTerbaru === []): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">
                                <i class="bi bi-cash-stack fs-4 d-block mb-1"></i>Belum ada pembayaran yang tercatat.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($pembayaranTerbaru as $b): ?>
                        <tr>
                            <td class="text-nowrap"><?= esc(tanggal_indo($b['paid_at'])) ?></td>
                            <td class="text-nowrap"><?= esc(periode_indo($b['period_month'], $b['period_year'])) ?></td>
                            <td class="text-end text-nowrap fw-semibold"><?= rupiah($b['amount']) ?></td>
                            <td class="text-center"><?= badge_metode($b['method']) ?></td>
                            <td class="text-end">
                                <a href="<?= base_url('penghuni/tagihan/kwitansi/' . $b['id']) ?>" target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-primary" title="Cetak kwitansi">
                                    <i class="bi bi-receipt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Kolom kanan -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-door-open me-2"></i>Kamar Saya</div>
            <div class="card-body">
                <?php if (! $adaKamar): ?>
                    <p class="text-secondary small mb-0">
                        Belum ada data kamar. Silakan hubungi pengelola kos.
                    </p>
                <?php else: ?>
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <span class="stat-icon text-bg-primary"><i class="bi bi-door-closed"></i></span>
                        <div>
                            <div class="fs-4 fw-bold lh-1"><?= esc($kamar['room_number']) ?></div>
                            <div class="small text-secondary">Lantai <?= (int) $kamar['floor'] ?></div>
                        </div>
                    </div>
                    <div class="small">
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-secondary">Harga Sewa</span>
                            <span class="fw-semibold"><?= rupiah($kamar['harga_kamar'] ?? $kamar['monthly_price']) ?> / bulan</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-secondary">Mulai Menempati</span>
                            <span><?= esc(tanggal_indo($kamar['start_date'])) ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-secondary">Lama Menempati</span>
                            <span><?= esc($durasi['teks']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom">
                            <span class="text-secondary">Deposit</span>
                            <span><?= rupiah($kamar['deposit'] ?? 0) ?></span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-secondary">Status Sewa</span>
                            <span><?= badge_sewa($aktif ? $sewa['status'] : ($kamar['status'] ?? 'selesai')) ?></span>
                        </div>
                    </div>
                    <?php if (! empty($kamar['facilities'])): ?>
                        <hr>
                        <div class="small text-secondary mb-1">Fasilitas</div>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach (preg_split('/[,;]+/', (string) $kamar['facilities']) as $fasilitas): ?>
                                <?php if (trim($fasilitas) !== ''): ?>
                                    <span class="badge text-bg-light border"><?= esc(trim($fasilitas)) ?></span>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (! $aktif): ?>
                        <div class="alert alert-secondary small mt-3 mb-0">
                            Kamar ini adalah riwayat terakhir Anda
                            <?= ! empty($kamar['end_date']) ? '(selesai ' . esc(tanggal_indo($kamar['end_date'])) . ')' : '' ?>.
                            Anda tidak sedang menempati kamar.
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3 kartu-ringkas">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-pie-chart me-2"></i>Status Pembayaran</div>
            <div class="card-body">
                <?php if ($ringkasanTagihan['total'] > 0): ?>
                    <div style="max-width:220px;margin:0 auto;">
                        <canvas id="grafikBayar" height="220"></canvas>
                    </div>
                    <div class="progress mt-3" style="height:8px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: <?= (float) persen($ringkasanTagihan['dibayar'], $ringkasanTagihan['total']) ?>%"></div>
                    </div>
                    <div class="small text-secondary text-center mt-2">
                        Terbayar <?= (float) persen($ringkasanTagihan['dibayar'], $ringkasanTagihan['total']) ?>%
                        dari total tagihan <?= rupiah($ringkasanTagihan['total']) ?>
                    </div>
                <?php else: ?>
                    <p class="text-secondary small mb-0 text-center">Belum ada tagihan untuk ditampilkan.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Informasi Kos</div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item">
                    <div class="text-secondary">Nama Kos</div>
                    <div class="fw-semibold"><?= esc(setting('kos_name', 'KosKu')) ?></div>
                </li>
                <?php if (setting('kos_address')): ?>
                    <li class="list-group-item">
                        <div class="text-secondary">Alamat</div>
                        <div><?= esc(setting('kos_address')) ?></div>
                    </li>
                <?php endif; ?>
                <?php if (setting('kos_owner')): ?>
                    <li class="list-group-item">
                        <div class="text-secondary">Pengelola</div>
                        <div><?= esc(setting('kos_owner')) ?></div>
                    </li>
                <?php endif; ?>
                <?php if (setting('kos_phone')): ?>
                    <li class="list-group-item">
                        <div class="text-secondary">Telepon / WhatsApp</div>
                        <div><?= esc(setting('kos_phone')) ?></div>
                    </li>
                <?php endif; ?>
                <?php if (setting('kos_bank')): ?>
                    <li class="list-group-item">
                        <div class="text-secondary mb-1">Rekening Pembayaran</div>
                        <div class="fw-semibold"><?= esc(setting('kos_bank')) ?></div>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<?php if ($ringkasanTagihan['total'] > 0): ?>
<script>
    // Grafik donat: perbandingan tagihan yang sudah dibayar dengan sisanya.
    (function () {
        const el = document.getElementById('grafikBayar');
        if (! el || typeof Chart === 'undefined') { return; }

        new Chart(el, {
            type: 'doughnut',
            data: {
                labels: ['Sudah Dibayar', 'Sisa Tagihan'],
                datasets: [{
                    data: [<?= (float) $ringkasanTagihan['dibayar'] ?>, <?= (float) $ringkasanTagihan['sisa'] ?>],
                    backgroundColor: ['#198754', '#dc3545'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '68%',
                // Animasi dimatikan agar grafik langsung tampil utuh
                // (animasi bergantung pada requestAnimationFrame yang bisa
                // tertahan saat tab tidak aktif).
                animation: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return ' ' + ctx.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(ctx.parsed);
                            }
                        }
                    }
                }
            }
        });
    })();
</script>
<?php endif; ?>
<?php $this->endSection() ?>

<?php endif; ?>
