<?php
/**
 * Preview layout admin (Tahap 1) - hanya untuk memeriksa tampilan.
 */
$this->extend('layouts/admin');
?>

<?php $this->section('content') ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle me-1"></i>
    Ini halaman <strong>preview layout</strong>. Belum ada data asli; sidebar, tabel, dan grafik
    di bawah hanya contoh tampilan.
</div>

<div class="row g-3 mb-4">
    <?php
    $kartu = [
        ['label' => 'Total Kamar', 'nilai' => '10', 'icon' => 'door-open', 'warna' => 'primary'],
        ['label' => 'Kamar Terisi', 'nilai' => '7', 'icon' => 'people-fill', 'warna' => 'success'],
        ['label' => 'Kamar Kosong', 'nilai' => '3', 'icon' => 'door-closed', 'warna' => 'secondary'],
        ['label' => 'Pemasukan Bulan Ini', 'nilai' => rupiah(7500000), 'icon' => 'cash-stack', 'warna' => 'info'],
    ];
    ?>
    <?php foreach ($kartu as $k): ?>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-<?= $k['warna'] ?>-subtle text-<?= $k['warna'] ?>">
                        <i class="bi bi-<?= $k['icon'] ?>"></i>
                    </div>
                    <div class="min-w-0">
                        <div class="text-secondary small"><?= esc($k['label']) ?></div>
                        <div class="fs-5 fw-bold text-truncate"><?= esc($k['nilai']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-graph-up me-2"></i>Grafik Pemasukan (contoh Chart.js)</div>
    <div class="card-body">
        <canvas id="grafikContoh" height="80"></canvas>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold"><i class="bi bi-table me-2"></i>Contoh Tabel</span>
        <button class="btn btn-sm btn-primary"><i class="bi bi-plus-lg me-1"></i>Tambah</button>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Penghuni</th><th>Kamar</th><th>Jatuh Tempo</th><th>Nominal</th><th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <tr>
                    <td>1</td><td>Budi Santoso</td><td>A-01</td><td><?= esc(tanggal_indo('2026-10-10')) ?></td>
                    <td><?= rupiah(800000) ?></td><td><?= badge_tagihan('lunas') ?></td>
                </tr>
                <tr>
                    <td>2</td><td>Siti Aminah</td><td>A-02</td><td><?= esc(tanggal_indo('2026-10-10')) ?></td>
                    <td><?= rupiah(850000) ?></td><td><?= badge_tagihan('belum_bayar') ?></td>
                </tr>
                <tr>
                    <td>3</td><td>Andi Wijaya</td><td>B-03</td><td><?= esc(tanggal_indo('2026-09-10')) ?></td>
                    <td><?= rupiah(750000) ?></td><td><?= badge_tagihan('terlambat') ?></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
    // Contoh grafik untuk memastikan Chart.js termuat.
    new Chart(document.getElementById('grafikContoh'), {
        type: 'line',
        data: {
            labels: ['Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt'],
            datasets: [{
                label: 'Pemasukan',
                data: [6200000, 6800000, 7100000, 7000000, 7400000, 7500000],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,.15)',
                fill: true,
                tension: .35
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
</script>
<?php $this->endSection() ?>
