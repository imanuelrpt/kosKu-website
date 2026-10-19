<?php
/**
 * Detail tagihan: informasi, ringkasan pembayaran, riwayat cicilan.
 * Variabel: $tagihan, $pembayaran
 */
$this->extend('layouts/admin');
?>

<?php $this->section('content') ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= base_url('tagihan') ?>" class="text-decoration-none">Tagihan</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= esc(nomor_invoice($tagihan)) ?></li>
    </ol>
</nav>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h5 mb-0">Detail Tagihan</h2>
        <div class="small text-secondary"><?= esc(nomor_invoice($tagihan)) ?></div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('tagihan') ?>" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <a href="<?= base_url('tagihan/cetak/' . $tagihan['id']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-printer me-1"></i>Cetak
        </a>
        <a href="<?= esc(wa_link($tagihan['tenant_phone'], pesan_wa_tagihan($tagihan)), 'attr') ?>" target="_blank"
           class="btn btn-sm btn-success">
            <i class="bi bi-whatsapp me-1"></i>Pengingat WA
        </a>
        <?php if ($tagihan['sisa'] > 0): ?>
            <a href="<?= base_url('pembayaran/catat?tagihan=' . $tagihan['id']) ?>" class="btn btn-sm btn-primary">
                <i class="bi bi-cash-coin me-1"></i>Catat Pembayaran
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-receipt me-2"></i>Rincian Tagihan</span>
                <?= badge_tagihan($tagihan['status']) ?>
            </div>
            <div class="card-body">
                <div class="row g-3 small">
                    <div class="col-6 col-md-4">
                        <div class="text-secondary">Penghuni</div>
                        <div class="fw-semibold">
                            <a href="<?= base_url('penghuni/' . $tagihan['tenant_id']) ?>" class="text-decoration-none">
                                <?= esc($tagihan['tenant_name']) ?>
                            </a>
                        </div>
                        <div class="text-secondary"><?= esc($tagihan['tenant_phone'] ?? 'Tanpa nomor HP') ?></div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-secondary">Kamar</div>
                        <div class="fw-semibold"><?= esc($tagihan['room_number']) ?> (Lantai <?= (int) $tagihan['floor'] ?>)</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-secondary">Periode</div>
                        <div class="fw-semibold"><?= esc(periode_indo($tagihan['period_month'], $tagihan['period_year'])) ?></div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-secondary">Jatuh Tempo</div>
                        <div class="fw-semibold"><?= esc(tanggal_indo($tagihan['due_date'])) ?></div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-secondary">Harga Sewa</div>
                        <div class="fw-semibold"><?= rupiah($tagihan['harga_kamar']) ?> / bulan</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-secondary">Dibuat</div>
                        <div class="fw-semibold"><?= esc(tanggal_indo($tagihan['created_at'])) ?></div>
                    </div>
                </div>

                <hr>

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-secondary small">Jumlah Tagihan</div>
                            <div class="fs-5 fw-bold"><?= rupiah($tagihan['amount']) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-secondary small">Sudah Dibayar</div>
                            <div class="fs-5 fw-bold text-success"><?= rupiah($tagihan['dibayar']) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-secondary small">Sisa Tagihan</div>
                            <div class="fs-5 fw-bold <?= $tagihan['sisa'] > 0 ? 'text-danger' : 'text-success' ?>">
                                <?= rupiah($tagihan['sisa']) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($tagihan['sisa'] > 0): ?>
                    <div class="progress mt-3" style="height:8px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: <?= (float) persen($tagihan['dibayar'], $tagihan['amount']) ?>%"></div>
                    </div>
                    <div class="small text-secondary mt-1">
                        Terbayar <?= (float) persen($tagihan['dibayar'], $tagihan['amount']) ?>% dari total tagihan.
                    </div>
                <?php else: ?>
                    <div class="alert alert-success d-flex align-items-center mt-3 mb-0">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Tagihan ini sudah lunas. Terima kasih.
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-clock-history me-2"></i>Riwayat Pembayaran
                <span class="badge text-bg-secondary ms-1"><?= count($pembayaran) ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal</th>
                            <th class="text-end">Jumlah</th>
                            <th class="text-center">Metode</th>
                            <th>Bukti</th>
                            <th>Catatan</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($pembayaran === []): ?>
                        <tr>
                            <td colspan="6" class="text-center text-secondary py-4">
                                Belum ada pembayaran untuk tagihan ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($pembayaran as $b): ?>
                        <tr>
                            <td class="text-nowrap"><?= esc(tanggal_indo($b['paid_at'])) ?></td>
                            <td class="text-end text-nowrap fw-semibold"><?= rupiah($b['amount']) ?></td>
                            <td class="text-center"><?= badge_metode($b['method']) ?></td>
                            <td>
                                <?php if (! empty($b['proof_file'])): ?>
                                    <a href="<?= esc(url_berkas('pembayaran', $b['proof_file']), 'attr') ?>" target="_blank"
                                       class="btn btn-sm btn-outline-secondary" title="Lihat bukti">
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
                                <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus pembayaran"
                                        data-bs-toggle="modal" data-bs-target="#modalHapus"
                                        data-hapus-url="<?= base_url('pembayaran/hapus/' . $b['id']) ?>"
                                        data-hapus-nama="pembayaran <?= rupiah($b['amount']) ?> tanggal <?= esc(tanggal_indo($b['paid_at']), 'attr') ?>"
                                        data-hapus-catatan="Status tagihan akan dihitung ulang setelah pembayaran dihapus.">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <?php if ($pembayaran !== []): ?>
                        <tfoot class="table-light">
                            <tr>
                                <th class="text-end">Total Dibayar</th>
                                <th class="text-end"><?= rupiah($tagihan['dibayar']) ?></th>
                                <th colspan="4"></th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person-badge me-2"></i>Penghuni</div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="avatar-initial"><?= esc(inisial_nama($tagihan['tenant_name'])) ?></span>
                    <div>
                        <div class="fw-semibold"><?= esc($tagihan['tenant_name']) ?></div>
                        <div class="small text-secondary">Kamar <?= esc($tagihan['room_number']) ?></div>
                    </div>
                </div>
                <div class="small">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-secondary">Telepon</span>
                        <span><?= esc($tagihan['tenant_phone'] ?? '-') ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-secondary">Mulai Menempati</span>
                        <span><?= esc(tanggal_indo($tagihan['occupancy_start'])) ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-secondary">Status Sewa</span>
                        <span><?= badge_sewa($tagihan['occupancy_status']) ?></span>
                    </div>
                </div>
                <a href="<?= base_url('penghuni/' . $tagihan['tenant_id']) ?>" class="btn btn-sm btn-outline-primary w-100 mt-3">
                    <i class="bi bi-person me-1"></i>Lihat Data Penghuni
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Tindakan</div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item">
                    Kirim pengingat lewat WhatsApp dengan pesan yang sudah disiapkan sistem.
                </li>
                <li class="list-group-item">
                    Pembayaran boleh sebagian (cicilan); status otomatis menjadi <strong>Lunas</strong> saat total pembayaran mencukupi.
                </li>
                <li class="list-group-item">
                    Tagihan hanya dapat dihapus bila belum ada pembayaran sama sekali.
                </li>
            </ul>
        </div>
    </div>
</div>

<?= view('partials/modal_hapus') ?>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<?= view('partials/script_hapus') ?>
<?php $this->endSection() ?>
