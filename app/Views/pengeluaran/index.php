<?php
/**
 * Daftar pengeluaran: ringkasan, filter, tabel, pagination.
 * Variabel: $pengeluaran, $pager, $cari, $kategori, $bulan, $tahun, $ringkasan, $ringkasBulan, $daftarTahun
 */
$this->extend('layouts/admin');

$opsiKategori = ['listrik' => 'Listrik', 'air' => 'Air', 'perbaikan' => 'Perbaikan', 'lainnya' => 'Lainnya'];
$adaFilter    = $cari !== '' || $kategori !== '' || $bulan > 0 || $tahun > 0;

$utilitas = (float) ($ringkasan['per_kategori']['listrik'] ?? 0) + (float) ($ringkasan['per_kategori']['air'] ?? 0);
?>

<?php $this->section('content') ?>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-danger"><i class="bi bi-wallet2"></i></span>
                <div>
                    <div class="text-secondary small">Total<?= $bulan > 0 || $tahun > 0 ? ' (terfilter)' : '' ?></div>
                    <div class="fs-6 fw-bold lh-1"><?= rupiah($ringkasan['total']) ?></div>
                    <div class="small text-secondary"><?= (int) $ringkasan['jumlah'] ?> catatan</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-warning"><i class="bi bi-calendar-month"></i></span>
                <div>
                    <div class="text-secondary small">Bulan Ini</div>
                    <div class="fs-6 fw-bold lh-1"><?= rupiah($ringkasBulan['total']) ?></div>
                    <div class="small text-secondary"><?= esc(bulan_indo(date('n')) . ' ' . date('Y')) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-info"><i class="bi bi-lightning-charge"></i></span>
                <div>
                    <div class="text-secondary small">Listrik &amp; Air</div>
                    <div class="fs-6 fw-bold lh-1"><?= rupiah($utilitas) ?></div>
                    <div class="small text-secondary">Utilitas</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-secondary"><i class="bi bi-tools"></i></span>
                <div>
                    <div class="text-secondary small">Perbaikan</div>
                    <div class="fs-6 fw-bold lh-1"><?= rupiah($ringkasan['per_kategori']['perbaikan'] ?? 0) ?></div>
                    <div class="small text-secondary">Perawatan</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
                    <form class="row g-2 align-items-center" method="get" action="<?= base_url('pengeluaran') ?>">
                        <div class="col-auto">
                            <input type="search" name="cari" class="form-control form-control-sm"
                                   placeholder="Cari catatan..." value="<?= esc($cari) ?>">
                        </div>
                        <div class="col-auto">
                            <select name="kategori" class="form-select form-select-sm">
                                <option value="">Semua kategori</option>
                                <?php foreach ($opsiKategori as $nilai => $label): ?>
                                    <option value="<?= esc($nilai, 'attr') ?>" <?= $kategori === $nilai ? 'selected' : '' ?>>
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
                            <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Cari</button>
                            <?php if ($adaFilter): ?>
                                <a href="<?= base_url('pengeluaran') ?>" class="btn btn-sm btn-light">Reset</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <a href="<?= base_url('pengeluaran/tambah') ?>" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-lg me-1"></i>Tambah Pengeluaran
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th class="text-end">Jumlah</th>
                            <th>Catatan</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($pengeluaran === []): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-5">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                <?= $adaFilter ? 'Tidak ada pengeluaran yang cocok dengan filter.' : 'Belum ada pengeluaran tercatat.' ?>
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($pengeluaran as $p): ?>
                        <tr>
                            <td class="text-nowrap">
                                <div class="fw-semibold"><?= esc(tanggal_indo($p['date'])) ?></div>
                                <div class="small text-secondary"><?= esc(bulan_indo((int) date('n', strtotime($p['date']))) . ' ' . date('Y', strtotime($p['date']))) ?></div>
                            </td>
                            <td><?= badge_kategori($p['category']) ?></td>
                            <td class="text-end text-nowrap fw-semibold"><?= rupiah($p['amount']) ?></td>
                            <td class="small"><?= esc($p['note'] ?? '-') ?></td>
                            <td class="text-end text-nowrap">
                                <a href="<?= base_url('pengeluaran/ubah/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary" title="Ubah">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus"
                                        data-bs-toggle="modal" data-bs-target="#modalHapus"
                                        data-hapus-url="<?= base_url('pengeluaran/hapus/' . $p['id']) ?>"
                                        data-hapus-nama="pengeluaran <?= esc(label_kategori($p['category']), 'attr') ?> sebesar <?= rupiah($p['amount']) ?>"
                                        data-hapus-catatan="Data pengeluaran yang dihapus tidak dapat dikembalikan.">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
                <div class="small text-secondary"><?= info_halaman($pager, 'pengeluaran') ?></div>
                <?= $pager->links('default', 'kosku') ?>
            </div>
        </div>

        <?= view('partials/modal_hapus') ?>
    </div>

    <div class="col-12 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-pie-chart me-2"></i>Rincian Kategori
            </div>
            <ul class="list-group list-group-flush">
                <?php foreach ($opsiKategori as $nilai => $label): ?>
                    <?php $nominal = (float) ($ringkasan['per_kategori'][$nilai] ?? 0); ?>
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center small">
                            <span><?= badge_kategori($nilai) ?></span>
                            <span class="fw-semibold"><?= rupiah($nominal) ?></span>
                        </div>
                        <div class="progress mt-2" style="height:5px;">
                            <div class="progress-bar" role="progressbar"
                                 style="width: <?= (float) persen($nominal, $ringkasan['total']) ?>%"></div>
                        </div>
                        <div class="small text-secondary mt-1"><?= (float) persen($nominal, $ringkasan['total']) ?>% dari total</div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<?= view('partials/script_hapus') ?>
<?php $this->endSection() ?>
