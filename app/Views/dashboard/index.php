<?php
/**
 * Dashboard admin: statistik, grafik, dan daftar tindak lanjut.
 * Variabel: $bulan, $tahun, $kamar, $penghuniTotal, $penghuniMenyewa, $sewaAktif,
 *           $jumlahSewaAktif, $tagihanBulan, $pemasukanBulan, $pengeluaranBulan,
 *           $labaBulan, $tunggakan, $tagihanTerlambat, $pembayaranTerbaru,
 *           $kamarKosong, $labelGrafik, $grafikMasuk, $grafikKeluar,
 *           $statusKamar, $statusTagihan, $pengingat
 */
$this->extend('layouts/admin');

$persenTerisi = persen($kamar['terisi'], $kamar['total']);
$totalTagihanBulan = (int) $tagihanBulan['jumlah'];
?>

<?php $this->section('content') ?>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h5 mb-0">Dashboard</h2>
        <div class="small text-secondary">
            <?= esc(tanggal_indo(date('Y-m-d'))) ?> &middot; Ringkasan <?= esc(periode_indo($bulan, $tahun)) ?>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('sewa/checkin') ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-box-arrow-in-right me-1"></i>Check-in
        </a>
        <a href="<?= base_url('tagihan') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-receipt me-1"></i>Tagihan
        </a>
        <a href="<?= base_url('pembayaran/catat') ?>" class="btn btn-sm btn-success">
            <i class="bi bi-cash-coin me-1"></i>Catat Pembayaran
        </a>
    </div>
</div>

<!-- Kartu statistik utama -->
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-primary"><i class="bi bi-door-open"></i></span>
                <div class="flex-grow-1">
                    <div class="text-secondary small">Kamar Terisi</div>
                    <div class="fs-5 fw-bold lh-1"><?= (int) $kamar['terisi'] ?> / <?= (int) $kamar['total'] ?></div>
                    <div class="progress mt-2" style="height:5px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $persenTerisi ?>%"></div>
                    </div>
                    <div class="small text-secondary mt-1"><?= $persenTerisi ?>% tingkat hunian</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-info"><i class="bi bi-people"></i></span>
                <div>
                    <div class="text-secondary small">Penghuni</div>
                    <div class="fs-5 fw-bold lh-1"><?= (int) $penghuniTotal ?></div>
                    <div class="small text-secondary"><?= (int) $penghuniMenyewa ?> sedang menyewa</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-success"><i class="bi bi-arrow-down-circle"></i></span>
                <div>
                    <div class="text-secondary small">Uang Masuk Bulan Ini</div>
                    <div class="fs-6 fw-bold lh-1 text-success"><?= rupiah($pemasukanBulan) ?></div>
                    <div class="small text-secondary">Pengeluaran <?= rupiah($pengeluaranBulan) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-<?= $labaBulan >= 0 ? 'primary' : 'warning' ?>">
                    <i class="bi bi-graph-up-arrow"></i>
                </span>
                <div>
                    <div class="text-secondary small">Laba / (Rugi) Bulan Ini</div>
                    <div class="fs-6 fw-bold lh-1 <?= $labaBulan >= 0 ? 'text-primary' : 'text-danger' ?>">
                        <?= rupiah($labaBulan) ?>
                    </div>
                    <div class="small text-secondary">Selisih uang masuk &amp; keluar</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kartu tindak lanjut -->
<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small">Tagihan Bulan Ini</div>
                        <div class="fs-6 fw-bold"><?= rupiah($tagihanBulan['total']) ?></div>
                        <div class="small text-secondary"><?= $totalTagihanBulan ?> tagihan terbit</div>
                    </div>
                    <span class="stat-icon text-bg-secondary"><i class="bi bi-receipt"></i></span>
                </div>
                <div class="d-flex flex-wrap gap-1 mt-2">
                    <span class="badge text-bg-success"><?= (int) $statusTagihan['lunas'] ?> lunas</span>
                    <span class="badge text-bg-secondary"><?= (int) $statusTagihan['belum_bayar'] ?> belum</span>
                    <span class="badge text-bg-danger"><?= (int) $statusTagihan['terlambat'] ?> telat</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small">Tunggakan (Semua Periode)</div>
                        <div class="fs-6 fw-bold text-danger"><?= rupiah($tunggakan) ?></div>
                        <div class="small text-secondary">Belum dibayar penghuni</div>
                    </div>
                    <span class="stat-icon text-bg-danger"><i class="bi bi-exclamation-octagon"></i></span>
                </div>
                <a href="<?= base_url('tagihan?status=terlambat') ?>" class="btn btn-sm btn-outline-danger w-100 mt-2">
                    <i class="bi bi-list-check me-1"></i>Lihat Tagihan Terlambat
                </a>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small">Kamar Kosong</div>
                        <div class="fs-5 fw-bold"><?= (int) $kamar['kosong'] ?></div>
                        <div class="small text-secondary">Siap disewakan</div>
                    </div>
                    <span class="stat-icon text-bg-success"><i class="bi bi-door-closed"></i></span>
                </div>
                <a href="<?= base_url('sewa/checkin') ?>" class="btn btn-sm btn-outline-success w-100 mt-2">
                    <i class="bi bi-plus-lg me-1"></i>Check-in Penghuni
                </a>
            </div>
        </div>
    </div>

    <div class="col-6 col-xl-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small">Diperbaiki</div>
                        <div class="fs-5 fw-bold"><?= (int) $kamar['perbaikan'] ?></div>
                        <div class="small text-secondary">Kamar dalam perbaikan</div>
                    </div>
                    <span class="stat-icon text-bg-warning"><i class="bi bi-tools"></i></span>
                </div>
                <a href="<?= base_url('kamar?status=perbaikan') ?>" class="btn btn-sm btn-outline-warning w-100 mt-2">
                    <i class="bi bi-wrench me-1"></i>Kelola Kamar
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Grafik -->
<div class="row g-3 mb-3">
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-bar-chart-line me-2"></i>Uang Masuk vs Pengeluaran (6 Bulan)
            </div>
            <div class="card-body">
                <div style="height:260px;"><canvas id="grafikKeuangan"></canvas></div>
            </div>
            <div class="card-footer bg-white small text-secondary">
                Uang masuk dihitung dari pembayaran yang diterima pada bulan tersebut.
                <a href="<?= base_url('laporan') ?>" class="text-decoration-none">Buka laporan lengkap</a>.
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-pie-chart me-2"></i>Status Kamar
            </div>
            <div class="card-body">
                <div style="height:210px;"><canvas id="grafikKamar"></canvas></div>
                <div class="small text-secondary text-center mt-2">
                    Total <?= (int) $kamar['total'] ?> kamar
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-receipt-cutoff me-2"></i>Status Tagihan Bulan Ini
            </div>
            <div class="card-body">
                <?php if ($totalTagihanBulan === 0): ?>
                    <p class="text-secondary small mb-0">
                        Belum ada tagihan untuk <?= esc(periode_indo($bulan, $tahun)) ?>.
                        <a href="<?= base_url('tagihan') ?>" class="text-decoration-none">Buat tagihan</a>.
                    </p>
                <?php else: ?>
                    <div style="height:210px;"><canvas id="grafikTagihan"></canvas></div>
                    <div class="small text-secondary text-center mt-2">
                        <?= $totalTagihanBulan ?> tagihan terbit
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Daftar tindak lanjut -->
<div class="row g-3 mb-3">
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    <i class="bi bi-exclamation-octagon text-danger me-2"></i>Tagihan Terlambat
                </span>
                <a href="<?= base_url('tagihan?status=terlambat') ?>" class="small text-decoration-none">Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Penghuni</th>
                            <th>Kamar</th>
                            <th>Periode</th>
                            <th class="text-end">Sisa</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($tagihanTerlambat === []): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">
                                <i class="bi bi-check-circle text-success me-1"></i>
                                Tidak ada tagihan terlambat. Semua pembayaran berjalan lancar.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($tagihanTerlambat as $t): ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?= esc($t['tenant_name']) ?></div>
                                <div class="small text-secondary">Jatuh tempo <?= esc(tanggal_indo($t['due_date'])) ?></div>
                            </td>
                            <td><?= esc($t['room_number']) ?></td>
                            <td class="small"><?= esc(periode_indo($t['period_month'], $t['period_year'])) ?></td>
                            <td class="text-end text-nowrap text-danger fw-semibold"><?= rupiah($t['sisa']) ?></td>
                            <td class="text-end text-nowrap">
                                <a href="<?= esc(wa_link($t['tenant_phone'], pesan_wa_tagihan($t)), 'attr') ?>" target="_blank"
                                   class="btn btn-sm btn-outline-success" title="Pengingat WhatsApp">
                                    <i class="bi bi-whatsapp"></i>
                                </a>
                                <a href="<?= base_url('tagihan/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-clock-history me-2"></i>Pembayaran Terbaru</span>
                <a href="<?= base_url('pembayaran') ?>" class="small text-decoration-none">Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Penghuni</th>
                            <th>Kamar</th>
                            <th class="text-center">Metode</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($pembayaranTerbaru === []): ?>
                        <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada pembayaran tercatat.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($pembayaranTerbaru as $b): ?>
                        <tr>
                            <td class="text-nowrap small"><?= esc(tanggal_indo($b['paid_at'])) ?></td>
                            <td class="fw-semibold"><?= esc($b['tenant_name']) ?></td>
                            <td><?= esc($b['room_number']) ?></td>
                            <td class="text-center"><?= badge_metode($b['method']) ?></td>
                            <td class="text-end text-nowrap fw-semibold"><?= rupiah($b['amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-door-closed me-2"></i>Kamar Kosong Siap Disewakan
            </div>
            <ul class="list-group list-group-flush">
                <?php if ($kamarKosong === []): ?>
                    <li class="list-group-item text-secondary small">
                        Tidak ada kamar kosong saat ini.
                    </li>
                <?php endif; ?>
                <?php foreach (array_slice($kamarKosong, 0, 5) as $k): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-semibold">Kamar <?= esc($k['room_number']) ?></div>
                            <div class="small text-secondary">
                                Lantai <?= (int) $k['floor'] ?> &middot; <?= rupiah($k['price']) ?> / bulan
                            </div>
                        </div>
                        <a href="<?= base_url('sewa/checkin?kamar=' . $k['id']) ?>" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Check-in
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if (count($kamarKosong) > 5): ?>
                <div class="card-footer bg-white small text-secondary">
                    dan <?= count($kamarKosong) - 5 ?> kamar kosong lainnya.
                    <a href="<?= base_url('kamar?status=kosong') ?>" class="text-decoration-none">Lihat semua</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-12 col-xl-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    <i class="bi bi-key me-2"></i>Sewa Aktif
                    <span class="badge text-bg-secondary ms-1"><?= (int) $jumlahSewaAktif ?></span>
                </span>
                <a href="<?= base_url('sewa') ?>" class="small text-decoration-none">Kelola sewa</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kamar</th>
                            <th>Penghuni</th>
                            <th>Mulai</th>
                            <th class="text-end">Harga Sewa</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($sewaAktif === []): ?>
                        <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada sewa aktif.</td></tr>
                    <?php endif; ?>
                    <?php foreach (array_slice($sewaAktif, 0, 6) as $s): ?>
                        <tr>
                            <td class="fw-semibold"><?= esc($s['room_number']) ?></td>
                            <td>
                                <a href="<?= base_url('penghuni/' . $s['tenant_id']) ?>" class="text-decoration-none">
                                    <?= esc($s['tenant_name']) ?>
                                </a>
                                <div class="small text-secondary"><?= esc($s['tenant_phone'] ?? '-') ?></div>
                            </td>
                            <td class="small text-nowrap"><?= esc(tanggal_indo($s['start_date'])) ?></td>
                            <td class="text-end text-nowrap fw-semibold"><?= rupiah($s['monthly_price']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
    // Grafik dashboard (Chart.js dimuat di layout admin).
    (function () {
        const gagalMuat = function (kanvas) {
            const isi = kanvas.closest('.card-body');
            if (isi) {
                isi.innerHTML = '<div class="alert alert-light border small mb-0">' +
                    'Grafik tidak dapat dimuat (koneksi ke CDN Chart.js gagal).</div>';
            }
        };

        if (typeof window.Chart === 'undefined') {
            document.querySelectorAll('canvas').forEach(gagalMuat);
            return;
        }

        const rupiahSingkat = (nilai) => 'Rp ' + new Intl.NumberFormat('id-ID').format(nilai);

        new Chart(document.getElementById('grafikKeuangan'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($labelGrafik, JSON_UNESCAPED_UNICODE) ?>,
                datasets: [
                    {
                        label: 'Uang Masuk',
                        data: <?= json_encode(array_map('floatval', $grafikMasuk)) ?>,
                        backgroundColor: 'rgba(25, 135, 84, .75)',
                        borderRadius: 6
                    },
                    {
                        label: 'Pengeluaran',
                        data: <?= json_encode(array_map('floatval', $grafikKeluar)) ?>,
                        backgroundColor: 'rgba(220, 53, 69, .65)',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => ctx.dataset.label + ': ' + rupiahSingkat(ctx.parsed.y)
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: (v) => new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(v) }
                    }
                }
            }
        });

        new Chart(document.getElementById('grafikKamar'), {
            type: 'doughnut',
            data: {
                labels: ['Terisi', 'Kosong', 'Perbaikan'],
                datasets: [{
                    data: [
                        <?= (int) $statusKamar['terisi'] ?>,
                        <?= (int) $statusKamar['kosong'] ?>,
                        <?= (int) $statusKamar['perbaikan'] ?>
                    ],
                    backgroundColor: ['#0d6efd', '#198754', '#ffc107'],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: { legend: { position: 'bottom' } }
            }
        });

        const kanvasTagihan = document.getElementById('grafikTagihan');
        if (kanvasTagihan) {
            new Chart(kanvasTagihan, {
                type: 'doughnut',
                data: {
                    labels: ['Lunas', 'Belum Bayar', 'Terlambat'],
                    datasets: [{
                        data: [
                            <?= (int) $statusTagihan['lunas'] ?>,
                            <?= (int) $statusTagihan['belum_bayar'] ?>,
                            <?= (int) $statusTagihan['terlambat'] ?>
                        ],
                        backgroundColor: ['#198754', '#adb5bd', '#dc3545'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '62%',
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }
    })();
</script>
<?php $this->endSection() ?>
