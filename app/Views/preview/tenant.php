<?php
/**
 * Preview layout portal penghuni (Tahap 1).
 */
$this->extend('layouts/tenant');
?>

<?php $this->section('content') ?>
<div class="alert alert-info">
    <i class="bi bi-info-circle me-1"></i>Ini halaman <strong>preview layout penghuni</strong> (contoh tampilan).
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">
                <div class="text-secondary small">Kamar Saya</div>
                <div class="fs-4 fw-bold">A-01 <span class="badge text-bg-primary align-middle fs-6">Terisi</span></div>
                <div class="text-secondary small mt-2">Sewa aktif sejak <?= esc(tanggal_indo('2026-01-05')) ?></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body">
                <div class="text-secondary small">Tagihan Aktif</div>
                <div class="fs-4 fw-bold"><?= rupiah(800000) ?> <?= badge_tagihan('belum_bayar') ?></div>
                <div class="text-secondary small mt-2">Jatuh tempo <?= esc(tanggal_indo('2026-10-10')) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-receipt me-2"></i>Riwayat Tagihan</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light"><tr><th>Periode</th><th>Nominal</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <tr>
                    <td><?= esc(periode_indo(9, 2026)) ?></td><td><?= rupiah(800000) ?></td>
                    <td><?= badge_tagihan('lunas') ?></td>
                    <td><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></button></td>
                </tr>
                <tr>
                    <td><?= esc(periode_indo(10, 2026)) ?></td><td><?= rupiah(800000) ?></td>
                    <td><?= badge_tagihan('belum_bayar') ?></td>
                    <td><button class="btn btn-sm btn-outline-secondary"><i class="bi bi-printer"></i></button></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $this->endSection() ?>
