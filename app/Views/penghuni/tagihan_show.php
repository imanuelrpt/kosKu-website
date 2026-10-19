<?php
/**
 * Detail satu tagihan milik penghuni (hanya baca).
 *
 * Variabel: $tenant, $tagihan, $pembayaran
 */
$this->extend('layouts/tenant');

$waPengelola = setting('kos_phone')
    ? wa_link(setting('kos_phone'), 'Halo ' . setting('kos_name', 'KosKu') . ', saya '
        . ($tenant['name'] ?? 'penghuni') . ' kamar ' . $tagihan['room_number']
        . ' ingin menanyakan tagihan ' . nomor_invoice($tagihan)
        . ' periode ' . periode_indo($tagihan['period_month'], $tagihan['period_year'])
        . ' sebesar ' . rupiah($tagihan['sisa']) . '.')
    : null;
?>

<?php $this->section('content') ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= base_url('penghuni/tagihan') ?>" class="text-decoration-none">Tagihan Saya</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= esc(nomor_invoice($tagihan)) ?></li>
    </ol>
</nav>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h5 mb-0">Detail Tagihan</h2>
        <div class="small text-secondary">
            <?= esc(nomor_invoice($tagihan)) ?> &middot;
            <?= esc(periode_indo($tagihan['period_month'], $tagihan['period_year'])) ?>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= base_url('penghuni/tagihan') ?>" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <a href="<?= base_url('penghuni/tagihan/cetak/' . $tagihan['id']) ?>" target="_blank" rel="noopener"
           class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-printer me-1"></i>Cetak / PDF
        </a>
        <?php if ($waPengelola !== null): ?>
            <a href="<?= esc($waPengelola, 'attr') ?>" target="_blank" rel="noopener" class="btn btn-sm btn-success">
                <i class="bi bi-whatsapp me-1"></i>Hubungi Pengelola
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span class="fw-semibold"><i class="bi bi-receipt me-2"></i>Rincian Tagihan</span>
                <?= badge_tagihan($tagihan['status']) ?>
            </div>
            <div class="card-body">
                <div class="row g-3 small">
                    <div class="col-6 col-md-4">
                        <div class="text-secondary">Kamar</div>
                        <div class="fw-semibold"><?= esc($tagihan['room_number']) ?> (Lantai <?= (int) $tagihan['floor'] ?>)</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-secondary">Periode Tagihan</div>
                        <div class="fw-semibold"><?= esc(periode_indo($tagihan['period_month'], $tagihan['period_year'])) ?></div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-secondary">Jatuh Tempo</div>
                        <div class="fw-semibold"><?= esc(tanggal_indo($tagihan['due_date'])) ?></div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-secondary">Tanggal Terbit</div>
                        <div class="fw-semibold"><?= esc(tanggal_indo($tagihan['created_at'])) ?></div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-secondary">Harga Sewa</div>
                        <div class="fw-semibold"><?= rupiah($tagihan['harga_kamar']) ?> / bulan</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-secondary">Mulai Menempati</div>
                        <div class="fw-semibold"><?= esc(tanggal_indo($tagihan['occupancy_start'])) ?></div>
                    </div>
                </div>

                <hr>

                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-secondary small">Jumlah Tagihan</div>
                            <div class="fs-6 fw-bold"><?= rupiah($tagihan['amount']) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-secondary small">Sudah Dibayar</div>
                            <div class="fs-6 fw-bold text-success"><?= rupiah($tagihan['dibayar']) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="text-secondary small">Sisa yang Harus Dibayar</div>
                            <div class="fs-6 fw-bold <?= $tagihan['sisa'] > 0 ? 'text-danger' : 'text-success' ?>">
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
                    <div class="small text-secondary mt-2">
                        Terbayar <?= (float) persen($tagihan['dibayar'], $tagihan['amount']) ?>% dari total tagihan.
                        Sisa dapat dibayar sekaligus atau dicicil.
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
                            <th>Catatan</th>
                            <th class="text-end">Kwitansi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($pembayaran === []): ?>
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">
                                Belum ada pembayaran untuk tagihan ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($pembayaran as $b): ?>
                        <tr>
                            <td class="text-nowrap"><?= esc(tanggal_indo($b['paid_at'])) ?></td>
                            <td class="text-end text-nowrap fw-semibold"><?= rupiah($b['amount']) ?></td>
                            <td class="text-center"><?= badge_metode($b['method']) ?></td>
                            <td class="small text-secondary"><?= esc($b['note'] ?? '-') ?></td>
                            <td class="text-end">
                                <a href="<?= base_url('penghuni/tagihan/kwitansi/' . $b['id']) ?>" target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-primary" title="Cetak kwitansi">
                                    <i class="bi bi-receipt"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <?php if ($pembayaran !== []): ?>
                        <tfoot class="table-light">
                            <tr>
                                <th></th>
                                <th class="text-end">Total Dibayar</th>
                                <th class="text-end"><?= rupiah($tagihan['dibayar']) ?></th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-credit-card-2-front me-2"></i>Cara Pembayaran</div>
            <div class="card-body small">
                <?php if (setting('kos_bank')): ?>
                    <div class="mb-3">
                        <div class="text-secondary">Transfer ke rekening</div>
                        <div class="fw-semibold"><?= esc(setting('kos_bank')) ?></div>
                    </div>
                <?php endif; ?>
                <ol class="ps-3 mb-0">
                    <li>Lakukan pembayaran (boleh sebagian) sesuai sisa tagihan.</li>
                    <li>Simpan bukti transfer.</li>
                    <li>
                        Kirim bukti transfer ke pengelola
                        <?php if ($waPengelola !== null): ?>
                            lewat WhatsApp <a href="<?= esc($waPengelola, 'attr') ?>" target="_blank" rel="noopener">di sini</a>
                        <?php else: ?>
                            kepada pengelola kos
                        <?php endif; ?>.
                    </li>
                    <li>Pengelola mencatat pembayaran dan kwitansi otomatis terbit di halaman ini.</li>
                </ol>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Ringkasan</div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-secondary">Nomor Tagihan</span>
                    <span class="fw-semibold"><?= esc(nomor_invoice($tagihan)) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-secondary">Status Sewa</span>
                    <span><?= badge_sewa($tagihan['occupancy_status']) ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between">
                    <span class="text-secondary">Terbilang</span>
                    <span class="text-end"><?= esc(terbilang($tagihan['sisa'] > 0 ? $tagihan['sisa'] : $tagihan['amount'])) ?></span>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php $this->endSection() ?>
