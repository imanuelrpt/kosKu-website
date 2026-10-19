<?php
/**
 * Laporan keuangan bulanan + ekspor CSV.
 * Variabel: $bulan, $tahun, $daftarTahun, $pemasukan, $pengeluaran, $tagihan,
 *           $totalPemasukan, $totalPengeluaran, $laba, $totalTagihan, $sisaTagihan,
 *           $perStatus, $perKategori, $perKamar, $pemasukanLalu, $pengeluaranLalu,
 *           $labelGrafik, $grafikMasuk, $grafikKeluar
 */
$this->extend('layouts/admin');

$opsiKategori = ['listrik' => 'Listrik', 'air' => 'Air', 'perbaikan' => 'Perbaikan', 'lainnya' => 'Lainnya'];
$pertumbuhan  = static function (float $sekarang, float $lalu): array {
    if ($lalu <= 0) {
        return [$sekarang > 0 ? 100.0 : 0.0, $sekarang > 0 ? 'baru' : 'sama'];
    }

    $selisih = (($sekarang - $lalu) / $lalu) * 100;

    return [round(abs($selisih), 1), $selisih >= 0 ? 'naik' : 'turun'];
};

[$naikMasuk, $arahMasuk]   = $pertumbuhan($totalPemasukan, (float) $pemasukanLalu);
[$naikKeluar, $arahKeluar] = $pertumbuhan($totalPengeluaran, (float) $pengeluaranLalu);

$periodeUrl = 'bulan=' . $bulan . '&tahun=' . $tahun;
?>

<?php $this->section('content') ?>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h5 mb-0">Laporan Keuangan</h2>
        <div class="small text-secondary">Periode <?= esc(periode_indo($bulan, $tahun)) ?></div>
    </div>
    <form class="row g-2 align-items-center" method="get" action="<?= base_url('laporan') ?>">
        <div class="col-auto">
            <select name="bulan" class="form-select form-select-sm">
                <?php for ($b = 1; $b <= 12; $b++): ?>
                    <option value="<?= $b ?>" <?= $bulan === $b ? 'selected' : '' ?>><?= esc(bulan_indo($b)) ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto">
            <select name="tahun" class="form-select form-select-sm">
                <?php foreach ($daftarTahun as $th): ?>
                    <option value="<?= (int) $th ?>" <?= $tahun === (int) $th ? 'selected' : '' ?>><?= (int) $th ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>Tampilkan</button>
        </div>
    </form>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon text-bg-success"><i class="bi bi-arrow-down-circle"></i></span>
                    <div>
                        <div class="text-secondary small">Uang Masuk</div>
                        <div class="fs-6 fw-bold lh-1 text-success"><?= rupiah($totalPemasukan) ?></div>
                    </div>
                </div>
                <div class="small mt-2">
                    <?php if ($arahMasuk === 'naik'): ?>
                        <span class="badge text-bg-success"><i class="bi bi-arrow-up-short"></i><?= $naikMasuk ?>%</span>
                    <?php elseif ($arahMasuk === 'turun'): ?>
                        <span class="badge text-bg-danger"><i class="bi bi-arrow-down-short"></i><?= $naikMasuk ?>%</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">baru</span>
                    <?php endif; ?>
                    <span class="text-secondary">vs bulan lalu (<?= rupiah($pemasukanLalu) ?>)</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon text-bg-danger"><i class="bi bi-arrow-up-circle"></i></span>
                    <div>
                        <div class="text-secondary small">Pengeluaran</div>
                        <div class="fs-6 fw-bold lh-1 text-danger"><?= rupiah($totalPengeluaran) ?></div>
                    </div>
                </div>
                <div class="small mt-2">
                    <?php if ($arahKeluar === 'naik'): ?>
                        <span class="badge text-bg-danger"><i class="bi bi-arrow-up-short"></i><?= $naikKeluar ?>%</span>
                    <?php elseif ($arahKeluar === 'turun'): ?>
                        <span class="badge text-bg-success"><i class="bi bi-arrow-down-short"></i><?= $naikKeluar ?>%</span>
                    <?php else: ?>
                        <span class="badge text-bg-secondary">baru</span>
                    <?php endif; ?>
                    <span class="text-secondary">vs bulan lalu (<?= rupiah($pengeluaranLalu) ?>)</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon text-bg-<?= $laba >= 0 ? 'primary' : 'warning' ?>">
                        <i class="bi bi-graph-up-arrow"></i>
                    </span>
                    <div>
                        <div class="text-secondary small">Laba / (Rugi)</div>
                        <div class="fs-6 fw-bold lh-1 <?= $laba >= 0 ? 'text-primary' : 'text-danger' ?>">
                            <?= rupiah($laba) ?>
                        </div>
                    </div>
                </div>
                <div class="small text-secondary mt-2">Uang masuk dikurangi pengeluaran</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <span class="stat-icon text-bg-warning"><i class="bi bi-hourglass-split"></i></span>
                    <div>
                        <div class="text-secondary small">Tunggakan Periode</div>
                        <div class="fs-6 fw-bold lh-1"><?= rupiah($sisaTagihan) ?></div>
                    </div>
                </div>
                <div class="small text-secondary mt-2">
                    Dari <?= rupiah($totalTagihan) ?> tagihan diterbitkan
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <span class="fw-semibold"><i class="bi bi-download me-2"></i>Ekspor Laporan (CSV / Excel)</span>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?= base_url('laporan/ekspor/ringkasan?' . $periodeUrl) ?>" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-file-earmark-text me-1"></i>Ringkasan
                </a>
                <a href="<?= base_url('laporan/ekspor/pemasukan?' . $periodeUrl) ?>" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Pemasukan
                </a>
                <a href="<?= base_url('laporan/ekspor/pengeluaran?' . $periodeUrl) ?>" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Pengeluaran
                </a>
                <a href="<?= base_url('laporan/ekspor/tagihan?' . $periodeUrl) ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>Tagihan
                </a>
            </div>
        </div>
        <div class="form-text mt-2">
            Berkas berformat CSV dengan pemisah titik koma (siap dibuka langsung di Excel).
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-bar-chart-line me-2"></i>Uang Masuk vs Pengeluaran (6 Bulan)
            </div>
            <div class="card-body">
                <div style="height:280px;"><canvas id="grafikKeuangan"></canvas></div>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-pie-chart me-2"></i>Komposisi Pengeluaran
            </div>
            <div class="card-body">
                <?php if ($totalPengeluaran <= 0): ?>
                    <p class="text-secondary small mb-0">Belum ada pengeluaran pada periode ini.</p>
                <?php else: ?>
                    <div style="height:280px;"><canvas id="grafikKategori"></canvas></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabPemasukan" type="button" role="tab">
                    <i class="bi bi-arrow-down-circle me-1"></i>Pemasukan <span class="badge text-bg-secondary"><?= count($pemasukan) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPengeluaran" type="button" role="tab">
                    <i class="bi bi-arrow-up-circle me-1"></i>Pengeluaran <span class="badge text-bg-secondary"><?= count($pengeluaran) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabTagihan" type="button" role="tab">
                    <i class="bi bi-receipt me-1"></i>Tagihan <span class="badge text-bg-secondary"><?= count($tagihan) ?></span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabKamar" type="button" role="tab">
                    <i class="bi bi-door-open me-1"></i>Per Kamar
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content">
        <!-- Pemasukan -->
        <div class="tab-pane fade show active" id="tabPemasukan" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Penghuni</th>
                            <th>Kamar</th>
                            <th>Periode Tagihan</th>
                            <th class="text-center">Metode</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($pemasukan === []): ?>
                        <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada pembayaran diterima pada periode ini.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($pemasukan as $p): ?>
                        <tr>
                            <td class="text-nowrap"><?= esc(tanggal_indo($p['paid_at'])) ?></td>
                            <td class="fw-semibold"><?= esc($p['tenant_name']) ?></td>
                            <td><?= esc($p['room_number']) ?></td>
                            <td><?= esc(periode_indo($p['period_month'], $p['period_year'])) ?></td>
                            <td class="text-center"><?= badge_metode($p['method']) ?></td>
                            <td class="text-end text-nowrap fw-semibold"><?= rupiah($p['amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="5" class="text-end">Total Uang Masuk</th>
                            <th class="text-end"><?= rupiah($totalPemasukan) ?></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Pengeluaran -->
        <div class="tab-pane fade" id="tabPengeluaran" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Catatan</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($pengeluaran === []): ?>
                        <tr><td colspan="4" class="text-center text-secondary py-4">Belum ada pengeluaran pada periode ini.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($pengeluaran as $p): ?>
                        <tr>
                            <td class="text-nowrap"><?= esc(tanggal_indo($p['date'])) ?></td>
                            <td><?= badge_kategori($p['category']) ?></td>
                            <td class="small"><?= esc($p['note'] ?? '-') ?></td>
                            <td class="text-end text-nowrap fw-semibold"><?= rupiah($p['amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="3" class="text-end">Total Pengeluaran</th>
                            <th class="text-end"><?= rupiah($totalPengeluaran) ?></th>
                        </tr>
                        <?php foreach ($opsiKategori as $nilai => $label): ?>
                            <?php if ((float) ($perKategori[$nilai] ?? 0) <= 0) { continue; } ?>
                            <tr>
                                <td colspan="3" class="text-end small text-secondary"><?= esc($label) ?></td>
                                <td class="text-end small"><?= rupiah($perKategori[$nilai]) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Tagihan -->
        <div class="tab-pane fade" id="tabTagihan" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Penghuni</th>
                            <th>Kamar</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-end">Dibayar</th>
                            <th class="text-end">Sisa</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($tagihan === []): ?>
                        <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada tagihan pada periode ini.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($tagihan as $t): ?>
                        <tr>
                            <td class="fw-semibold">
                                <a href="<?= base_url('tagihan/' . $t['id']) ?>" class="text-decoration-none">
                                    <?= esc($t['tenant_name']) ?>
                                </a>
                            </td>
                            <td><?= esc($t['room_number']) ?></td>
                            <td class="text-end text-nowrap"><?= rupiah($t['amount']) ?></td>
                            <td class="text-end text-nowrap text-success"><?= rupiah($t['dibayar']) ?></td>
                            <td class="text-end text-nowrap <?= $t['sisa'] > 0 ? 'text-danger fw-semibold' : 'text-secondary' ?>">
                                <?= rupiah($t['sisa']) ?>
                            </td>
                            <td class="text-nowrap"><?= esc(tanggal_indo($t['due_date'])) ?></td>
                            <td class="text-center"><?= badge_tagihan($t['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="2" class="text-end">Total</th>
                            <th class="text-end"><?= rupiah($totalTagihan) ?></th>
                            <th colspan="2" class="text-end">
                                Sisa: <?= rupiah($sisaTagihan) ?>
                            </th>
                            <th colspan="2" class="small text-secondary">
                                <?= (int) ($perStatus['lunas'] ?? 0) ?> lunas &middot;
                                <?= (int) ($perStatus['belum_bayar'] ?? 0) ?> belum bayar &middot;
                                <?= (int) ($perStatus['terlambat'] ?? 0) ?> terlambat
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Per kamar -->
        <div class="tab-pane fade" id="tabKamar" role="tabpanel">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kamar</th>
                            <th>Penghuni</th>
                            <th class="text-center">Jumlah Pembayaran</th>
                            <th class="text-end">Total Diterima</th>
                            <th class="text-end">Kontribusi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($perKamar === []): ?>
                        <tr><td colspan="5" class="text-center text-secondary py-4">Belum ada pemasukan per kamar pada periode ini.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($perKamar as $k): ?>
                        <tr>
                            <td class="fw-semibold"><?= esc($k['room_number']) ?></td>
                            <td><?= esc($k['tenant_name']) ?></td>
                            <td class="text-center"><?= (int) $k['jumlah'] ?>x</td>
                            <td class="text-end text-nowrap fw-semibold"><?= rupiah($k['total']) ?></td>
                            <td class="text-end" style="min-width:140px;">
                                <div class="progress" style="height:6px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                         style="width: <?= (float) persen($k['total'], $totalPemasukan) ?>%"></div>
                                </div>
                                <div class="small text-secondary"><?= (float) persen($k['total'], $totalPemasukan) ?>%</div>
                            </td>
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
    // Grafik laporan bulanan (Chart.js dimuat di layout).
    (function () {
        const label   = <?= json_encode($labelGrafik, JSON_UNESCAPED_UNICODE) ?>;
        const masuk    = <?= json_encode(array_map('floatval', $grafikMasuk)) ?>;
        const keluar   = <?= json_encode(array_map('floatval', $grafikKeluar)) ?>;

        if (typeof window.Chart === 'undefined') {
            document.querySelectorAll('canvas').forEach(function (c) {
                c.closest('.card-body').innerHTML =
                    '<div class="alert alert-light border small mb-0">Grafik tidak dapat dimuat karena koneksi ke CDN Chart.js gagal.</div>';
            });
            return;
        }

        const rupiahSingkat = (nilai) => 'Rp ' + new Intl.NumberFormat('id-ID').format(nilai);

        new Chart(document.getElementById('grafikKeuangan'), {
            type: 'bar',
            data: {
                labels: label,
                datasets: [
                    {
                        label: 'Uang Masuk',
                        data: masuk,
                        backgroundColor: 'rgba(25, 135, 84, .75)',
                        borderRadius: 6
                    },
                    {
                        label: 'Pengeluaran',
                        data: keluar,
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

        const canvasKategori = document.getElementById('grafikKategori');
        if (canvasKategori) {
            new Chart(canvasKategori, {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode(array_values(array_map(static fn ($k) => label_kategori($k), array_keys($opsiKategori))), JSON_UNESCAPED_UNICODE) ?>,
                    datasets: [{
                        data: <?= json_encode(array_map('floatval', array_values($perKategori))) ?>,
                        backgroundColor: ['#ffc107', '#0dcaf0', '#dc3545', '#6c757d'],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ctx.label + ': ' + rupiahSingkat(ctx.parsed)
                            }
                        }
                    }
                }
            });
        }
    })();
</script>
<?php $this->endSection() ?>
