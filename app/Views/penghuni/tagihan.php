<?php
/**
 * Tagihan Saya - daftar tagihan milik penghuni yang sedang login.
 *
 * Variabel: $tenant, $tagihan, $pager, $ringkasan, $ringkasanBayar,
 *           $berikutnya, $status, $bulan, $tahun, $daftarTahun
 */
$this->extend('layouts/tenant');

$opsiStatus = ['belum_bayar' => 'Belum Bayar', 'lunas' => 'Lunas', 'terlambat' => 'Terlambat'];
$adaFilter  = $status !== '' || $bulan > 0 || $tahun > 0;
?>

<?php if ($tenant === null): ?>
    <?php $this->section('content') ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center p-5">
            <i class="bi bi-person-plus fs-1 text-warning d-block mb-3"></i>
            <h1 class="h4">Akun Anda Belum Tertaut</h1>
            <p class="text-secondary mb-0">
                Tagihan akan tampil di sini setelah pengelola kos menautkan akun Anda ke data penghuni.
            </p>
        </div>
    </div>
    <?php $this->endSection() ?>

<?php else: ?>

<?php $this->section('content') ?>

<?php if ($berikutnya !== null): ?>
    <div class="alert alert-warning d-flex flex-wrap align-items-center gap-2 shadow-sm" role="alert">
        <i class="bi bi-alarm-fill fs-5"></i>
        <div class="flex-grow-1">
            Tagihan terdekat: periode <strong><?= esc(periode_indo($berikutnya['period_month'], $berikutnya['period_year'])) ?></strong>
            sebesar <strong><?= rupiah($berikutnya['sisa']) ?></strong>,
            jatuh tempo <strong><?= esc(tanggal_indo($berikutnya['due_date'])) ?></strong>.
        </div>
        <a href="<?= base_url('penghuni/tagihan/' . $berikutnya['id']) ?>" class="btn btn-sm btn-dark">
            Lihat Tagihan
        </a>
    </div>
<?php endif; ?>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-primary"><i class="bi bi-receipt"></i></span>
                <div>
                    <div class="text-secondary small">Total Tagihan<?= $adaFilter ? ' (terfilter)' : '' ?></div>
                    <div class="fs-6 fw-bold lh-1"><?= rupiah($ringkasan['total']) ?></div>
                    <div class="small text-secondary"><?= (int) $ringkasan['jumlah'] ?> tagihan</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-success"><i class="bi bi-cash-coin"></i></span>
                <div>
                    <div class="text-secondary small">Sudah Dibayar</div>
                    <div class="fs-6 fw-bold lh-1 text-success"><?= rupiah($ringkasan['dibayar']) ?></div>
                    <div class="small text-secondary"><?= (int) ($ringkasan['per_status']['lunas'] ?? 0) ?> tagihan lunas</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-warning"><i class="bi bi-hourglass-split"></i></span>
                <div>
                    <div class="text-secondary small">Sisa Tagihan</div>
                    <div class="fs-6 fw-bold lh-1 text-warning-emphasis"><?= rupiah($ringkasan['sisa']) ?></div>
                    <div class="small text-secondary"><?= (int) $ringkasan['jumlah'] - (int) ($ringkasan['per_status']['lunas'] ?? 0) ?> belum lunas</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-danger"><i class="bi bi-exclamation-octagon"></i></span>
                <div>
                    <div class="text-secondary small">Terlambat</div>
                    <div class="fs-4 fw-bold lh-1"><?= (int) $ringkasan['terlambat'] ?></div>
                    <div class="small text-secondary">Total <?= rupiah($ringkasanBayar['total']) ?> dibayar</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <form class="row g-2 align-items-center" method="get" action="<?= base_url('penghuni/tagihan') ?>">
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua status</option>
                    <?php foreach ($opsiStatus as $nilai => $label): ?>
                        <option value="<?= esc($nilai, 'attr') ?>" <?= $status === $nilai ? 'selected' : '' ?>>
                            <?= esc($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="bulan" class="form-select form-select-sm">
                    <option value="">Semua bulan</option>
                    <?php for ($b = 1; $b <= 12; $b++): ?>
                        <option value="<?= $b ?>" <?= $bulan === $b ? 'selected' : '' ?>><?= esc(bulan_indo($b)) ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <select name="tahun" class="form-select form-select-sm">
                    <option value="">Semua tahun</option>
                    <?php foreach ($daftarTahun as $th): ?>
                        <option value="<?= (int) $th ?>" <?= $tahun === (int) $th ? 'selected' : '' ?>><?= (int) $th ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-funnel me-1"></i>Terapkan</button>
                <?php if ($adaFilter): ?>
                    <a href="<?= base_url('penghuni/tagihan') ?>" class="btn btn-sm btn-light">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Periode</th>
                    <th class="text-end">Jumlah</th>
                    <th class="text-end d-none d-lg-table-cell">Dibayar</th>
                    <th class="text-end">Sisa</th>
                    <th>Jatuh Tempo</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($tagihan === []): ?>
                <tr>
                    <td colspan="7" class="text-center text-secondary py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        <?= $adaFilter ? 'Tidak ada tagihan yang cocok dengan filter.' : 'Belum ada tagihan untuk akun Anda.' ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($tagihan as $t): ?>
                <tr>
                    <td class="text-nowrap fw-semibold"><?= esc(periode_indo($t['period_month'], $t['period_year'])) ?></td>
                    <td class="text-end text-nowrap"><?= rupiah($t['amount']) ?></td>
                    <td class="text-end text-nowrap text-success d-none d-lg-table-cell"><?= rupiah($t['dibayar']) ?></td>
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

    <div class="card-footer bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div class="small text-secondary"><?= info_halaman($pager, 'tagihan') ?></div>
        <?= $pager->links('default', 'kosku') ?>
    </div>
</div>

<?php $this->endSection() ?>
<?php endif; ?>
