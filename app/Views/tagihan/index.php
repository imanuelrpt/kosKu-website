<?php
/**
 * Daftar tagihan: ringkasan, filter, tabel, pagination.
 * Variabel: $tagihan, $pager, $cari, $status, $bulan, $tahun, $ringkasan, $tunggakan, $daftarTahun
 */
$this->extend('layouts/admin');

$opsiStatus = ['belum_bayar' => 'Belum Bayar', 'lunas' => 'Lunas', 'terlambat' => 'Terlambat'];
$adaFilter  = $cari !== '' || $status !== '' || $bulan > 0 || $tahun > 0;
?>

<?php $this->section('content') ?>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-primary"><i class="bi bi-receipt"></i></span>
                <div>
                    <div class="text-secondary small">Tagihan<?= $bulan > 0 || $tahun > 0 ? ' (terfilter)' : '' ?></div>
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
                    <div class="small text-secondary"><?= (int) ($ringkasan['per_status']['lunas'] ?? 0) ?> lunas</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-warning"><i class="bi bi-hourglass-split"></i></span>
                <div>
                    <div class="text-secondary small">Belum Terbayar</div>
                    <div class="fs-6 fw-bold lh-1 text-warning-emphasis"><?= rupiah($ringkasan['sisa']) ?></div>
                    <div class="small text-secondary"><?= (int) ($ringkasan['per_status']['belum_bayar'] ?? 0) ?> belum bayar</div>
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
                    <div class="small text-secondary">Total tunggakan <?= rupiah($tunggakan) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <form class="row g-2 align-items-center" method="get" action="<?= base_url('tagihan') ?>">
                <div class="col-auto">
                    <input type="search" name="cari" class="form-control form-control-sm"
                           placeholder="Cari nama / kamar / HP..." value="<?= esc($cari) ?>">
                </div>
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
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-search me-1"></i>Cari</button>
                    <?php if ($adaFilter): ?>
                        <a href="<?= base_url('tagihan') ?>" class="btn btn-sm btn-light">Reset</a>
                    <?php endif; ?>
                </div>
            </form>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalGenerate">
                    <i class="bi bi-magic me-1"></i>Buat Otomatis
                </button>
                <a href="<?= base_url('tagihan/buat') ?>" class="btn btn-sm btn-success">
                    <i class="bi bi-plus-lg me-1"></i>Buat Manual
                </a>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Penghuni</th>
                    <th>Kamar</th>
                    <th>Periode</th>
                    <th class="text-end">Jumlah</th>
                    <th class="text-end">Dibayar</th>
                    <th class="text-end">Sisa</th>
                    <th>Jatuh Tempo</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($tagihan === []): ?>
                <tr>
                    <td colspan="9" class="text-center text-secondary py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        <?= $adaFilter ? 'Tidak ada tagihan yang cocok dengan filter.' : 'Belum ada tagihan. Gunakan tombol "Buat Otomatis" untuk membuat tagihan bulan ini.' ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($tagihan as $t): ?>
                <tr>
                    <td>
                        <a href="<?= base_url('penghuni/' . $t['tenant_id']) ?>" class="text-decoration-none fw-semibold">
                            <?= esc($t['tenant_name']) ?>
                        </a>
                        <div class="small text-secondary"><?= esc($t['tenant_phone'] ?? 'Tanpa nomor HP') ?></div>
                    </td>
                    <td>
                        <div class="fw-semibold"><?= esc($t['room_number']) ?></div>
                        <div class="small text-secondary">Lantai <?= (int) $t['floor'] ?></div>
                    </td>
                    <td class="text-nowrap"><?= esc(periode_indo($t['period_month'], $t['period_year'])) ?></td>
                    <td class="text-end text-nowrap"><?= rupiah($t['amount']) ?></td>
                    <td class="text-end text-nowrap text-success"><?= rupiah($t['dibayar']) ?></td>
                    <td class="text-end text-nowrap <?= $t['sisa'] > 0 ? 'text-danger fw-semibold' : 'text-secondary' ?>">
                        <?= rupiah($t['sisa']) ?>
                    </td>
                    <td class="text-nowrap"><?= esc(tanggal_indo($t['due_date'])) ?></td>
                    <td class="text-center"><?= badge_tagihan($t['status']) ?></td>
                    <td class="text-end text-nowrap">
                        <?php if ($t['sisa'] > 0): ?>
                            <a href="<?= base_url('pembayaran/catat?tagihan=' . $t['id']) ?>"
                               class="btn btn-sm btn-outline-success" title="Catat pembayaran">
                                <i class="bi bi-cash-coin"></i>
                            </a>
                        <?php endif; ?>
                        <a href="<?= base_url('tagihan/' . $t['id']) ?>" class="btn btn-sm btn-outline-primary" title="Detail tagihan">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="<?= base_url('tagihan/cetak/' . $t['id']) ?>" target="_blank"
                           class="btn btn-sm btn-outline-secondary" title="Cetak tagihan">
                            <i class="bi bi-printer"></i>
                        </a>
                        <a href="<?= esc(wa_link($t['tenant_phone'], pesan_wa_tagihan($t)), 'attr') ?>" target="_blank"
                           class="btn btn-sm btn-outline-success" title="Kirim pengingat WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                        <?php if ($t['dibayar'] <= 0): ?>
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus tagihan"
                                    data-bs-toggle="modal" data-bs-target="#modalHapus"
                                    data-hapus-url="<?= base_url('tagihan/hapus/' . $t['id']) ?>"
                                    data-hapus-nama="tagihan <?= esc($t['tenant_name'], 'attr') ?> periode <?= esc(periode_indo($t['period_month'], $t['period_year']), 'attr') ?>"
                                    data-hapus-catatan="Tagihan yang dihapus tidak dapat dikembalikan. Data yang sudah dibayar tidak boleh dihapus.">
                                <i class="bi bi-trash"></i>
                            </button>
                        <?php endif; ?>
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

<!-- Modal: buat tagihan otomatis -->
<div class="modal fade" id="modalGenerate" tabindex="-1" aria-labelledby="modalGenerateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <?= form_open('tagihan/generate') ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGenerateLabel">
                        <i class="bi bi-magic text-primary me-2"></i>Buat Tagihan Otomatis
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-secondary">
                        Sistem membuat tagihan untuk <strong>semua penghuni yang sedang menyewa</strong> pada periode
                        yang dipilih. Tagihan yang sudah ada tidak akan dibuat ganda.
                    </p>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label" for="gen_bulan">Bulan</label>
                            <select name="period_month" id="gen_bulan" class="form-select">
                                <?php for ($b = 1; $b <= 12; $b++): ?>
                                    <option value="<?= $b ?>" <?= (int) date('n') === $b ? 'selected' : '' ?>>
                                        <?= esc(bulan_indo($b)) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="gen_tahun">Tahun</label>
                            <input type="number" name="period_year" id="gen_tahun" class="form-control"
                                   value="<?= date('Y') ?>" min="2000" max="2100">
                        </div>
                    </div>
                    <div class="form-text mt-2">
                        Jatuh tempo mengikuti pengaturan tanggal <?= (int) setting('invoice_due_day', 10) ?> bulan berikutnya.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-magic me-1"></i>Buat Tagihan</button>
                </div>
            <?= form_close() ?>
        </div>
    </div>
</div>

<?= view('partials/modal_hapus') ?>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<?= view('partials/script_hapus') ?>
<?php $this->endSection() ?>
