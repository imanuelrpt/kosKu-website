<?php
/**
 * Daftar pembayaran: ringkasan, filter, tabel, pagination.
 * Variabel: $pembayaran, $pager, $cari, $metode, $bulan, $tahun, $ringkasan, $tunggakan, $daftarTahun
 */
$this->extend('layouts/admin');

$opsiMetode = ['tunai' => 'Tunai', 'transfer' => 'Transfer'];
$adaFilter  = $cari !== '' || $metode !== '' || $bulan > 0 || $tahun > 0;
?>

<?php $this->section('content') ?>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-primary"><i class="bi bi-cash-stack"></i></span>
                <div>
                    <div class="text-secondary small">Total Diterima<?= $bulan > 0 || $tahun > 0 ? ' (terfilter)' : '' ?></div>
                    <div class="fs-6 fw-bold lh-1"><?= rupiah($ringkasan['total']) ?></div>
                    <div class="small text-secondary"><?= (int) $ringkasan['jumlah'] ?> transaksi</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-info"><i class="bi bi-bank"></i></span>
                <div>
                    <div class="text-secondary small">Transfer</div>
                    <div class="fs-6 fw-bold lh-1"><?= rupiah($ringkasan['transfer']) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-success"><i class="bi bi-wallet2"></i></span>
                <div>
                    <div class="text-secondary small">Tunai</div>
                    <div class="fs-6 fw-bold lh-1"><?= rupiah($ringkasan['tunai']) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-danger"><i class="bi bi-exclamation-octagon"></i></span>
                <div>
                    <div class="text-secondary small">Total Tunggakan</div>
                    <div class="fs-6 fw-bold lh-1"><?= rupiah($tunggakan) ?></div>
                    <div class="small text-secondary">Semua periode</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <form class="row g-2 align-items-center" method="get" action="<?= base_url('pembayaran') ?>">
                <div class="col-auto">
                    <input type="search" name="cari" class="form-control form-control-sm"
                           placeholder="Cari nama / kamar / HP..." value="<?= esc($cari) ?>">
                </div>
                <div class="col-auto">
                    <select name="metode" class="form-select form-select-sm">
                        <option value="">Semua metode</option>
                        <?php foreach ($opsiMetode as $nilai => $label): ?>
                            <option value="<?= esc($nilai, 'attr') ?>" <?= $metode === $nilai ? 'selected' : '' ?>>
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
                        <a href="<?= base_url('pembayaran') ?>" class="btn btn-sm btn-light">Reset</a>
                    <?php endif; ?>
                </div>
            </form>

            <a href="<?= base_url('pembayaran/catat') ?>" class="btn btn-sm btn-success">
                <i class="bi bi-plus-lg me-1"></i>Catat Pembayaran
            </a>
        </div>
    </div>

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
                    <th class="text-center">Bukti</th>
                    <th>Catatan</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($pembayaran === []): ?>
                <tr>
                    <td colspan="9" class="text-center text-secondary py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        <?= $adaFilter ? 'Tidak ada pembayaran yang cocok dengan filter.' : 'Belum ada pembayaran tercatat.' ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($pembayaran as $b): ?>
                <tr>
                    <td class="text-nowrap">
                        <div class="fw-semibold"><?= esc(tanggal_indo($b['paid_at'])) ?></div>
                        <div class="small text-secondary">#<?= (int) $b['id'] ?></div>
                    </td>
                    <td>
                        <a href="<?= base_url('penghuni/' . $b['tenant_id']) ?>" class="text-decoration-none fw-semibold">
                            <?= esc($b['tenant_name']) ?>
                        </a>
                        <div class="small text-secondary"><?= esc($b['tenant_phone'] ?? 'Tanpa nomor HP') ?></div>
                    </td>
                    <td class="fw-semibold"><?= esc($b['room_number']) ?></td>
                    <td class="text-nowrap">
                        <?= esc(periode_indo($b['period_month'], $b['period_year'])) ?>
                        <div class="small"><?= badge_tagihan($b['invoice_status']) ?></div>
                    </td>
                    <td class="text-center"><?= badge_metode($b['method']) ?></td>
                    <td class="text-end text-nowrap fw-semibold"><?= rupiah($b['amount']) ?></td>
                    <td class="text-center">
                        <?php if (! empty($b['proof_file'])): ?>
                            <a href="<?= esc(url_berkas('pembayaran', $b['proof_file']), 'attr') ?>" target="_blank"
                               class="btn btn-sm btn-outline-secondary" title="Lihat bukti transfer">
                                <i class="bi bi-paperclip"></i>
                            </a>
                        <?php else: ?>
                            <span class="text-secondary small">&mdash;</span>
                        <?php endif; ?>
                    </td>
                    <td class="small text-secondary"><?= esc($b['note'] ?? '-') ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= base_url('pembayaran/kwitansi/' . $b['id']) ?>" target="_blank"
                           class="btn btn-sm btn-outline-primary" title="Cetak kwitansi">
                            <i class="bi bi-receipt"></i>
                        </a>
                        <a href="<?= esc(wa_link($b['tenant_phone'], pesan_wa_kwitansi($b)), 'attr') ?>" target="_blank"
                           class="btn btn-sm btn-outline-success" title="Konfirmasi WhatsApp">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus pembayaran"
                                data-bs-toggle="modal" data-bs-target="#modalHapus"
                                data-hapus-url="<?= base_url('pembayaran/hapus/' . $b['id']) ?>"
                                data-hapus-nama="pembayaran <?= rupiah($b['amount']) ?> dari <?= esc($b['tenant_name'], 'attr') ?>"
                                data-hapus-catatan="Status tagihan akan dihitung ulang setelah pembayaran dihapus.">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div class="small text-secondary"><?= info_halaman($pager, 'pembayaran') ?></div>
        <?= $pager->links('default', 'kosku') ?>
    </div>
</div>

<?= view('partials/modal_hapus') ?>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<?= view('partials/script_hapus') ?>
<?php $this->endSection() ?>
