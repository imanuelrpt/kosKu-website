<?php
/**
 * Detail penghuni: data diri, kamar aktif, riwayat sewa, tagihan, pembayaran.
 */
$this->extend('layouts/admin');
?>

<?php $this->section('content') ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= base_url('penghuni') ?>" class="text-decoration-none">Data Penghuni</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= esc($penghuni['name']) ?></li>
    </ol>
</nav>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">Detail Penghuni</h2>
    <div class="d-flex gap-2">
        <a href="<?= base_url('penghuni') ?>" class="btn btn-sm btn-light">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
        <?php if ($sewaAktif !== null): ?>
            <a href="<?= base_url('sewa/checkout/' . $sewaAktif['id']) ?>" class="btn btn-sm btn-warning">
                <i class="bi bi-box-arrow-right me-1"></i>Check-out
            </a>
        <?php else: ?>
            <a href="<?= base_url('sewa/checkin?penghuni=' . $penghuni['id']) ?>" class="btn btn-sm btn-success">
                <i class="bi bi-box-arrow-in-right me-1"></i>Check-in
            </a>
        <?php endif; ?>
        <a href="<?= base_url('penghuni/ubah/' . $penghuni['id']) ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-pencil me-1"></i>Ubah Data
        </a>
        <button type="button" class="btn btn-sm btn-outline-danger"
                data-bs-toggle="modal" data-bs-target="#modalHapus"
                data-hapus-url="<?= base_url('penghuni/hapus/' . $penghuni['id']) ?>"
                data-hapus-nama="penghuni <?= esc($penghuni['name']) ?>"
                data-hapus-catatan="Riwayat sewa dan pembayaran penghuni ini tetap tersimpan di sistem.">
            <i class="bi bi-trash me-1"></i>Hapus
        </button>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom kiri: data diri -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <?php if (! empty($penghuni['photo'])): ?>
                    <img src="<?= esc(url_berkas('penghuni', $penghuni['photo']), 'attr') ?>"
                         alt="Foto <?= esc($penghuni['name'], 'attr') ?>"
                         class="rounded-circle border mb-3" style="width:110px;height:110px;object-fit:cover;">
                <?php else: ?>
                    <span class="avatar-initial mb-3" style="width:110px;height:110px;font-size:2rem;">
                        <?= esc(inisial_nama($penghuni['name'])) ?>
                    </span>
                <?php endif; ?>

                <h3 class="h5 mb-1"><?= esc($penghuni['name']) ?></h3>
                <p class="text-secondary small mb-3">
                    <?= esc($penghuni['phone'] ?? 'Tanpa nomor HP') ?>
                </p>

                <?php if ($sewaAktif !== null): ?>
                    <span class="badge text-bg-success">
                        <i class="bi bi-house-door me-1"></i>Kamar <?= esc($sewaAktif['room_number']) ?>
                    </span>
                <?php else: ?>
                    <span class="badge text-bg-secondary">Tidak sedang menyewa</span>
                <?php endif; ?>
            </div>

            <ul class="list-group list-group-flush small">
                <li class="list-group-item d-flex justify-content-between gap-3">
                    <span class="text-secondary">Nomor KTP</span>
                    <span class="fw-semibold text-end"><?= esc($penghuni['id_number'] ?? '-') ?></span>
                </li>
                <li class="list-group-item d-flex justify-content-between gap-3">
                    <span class="text-secondary">Kontak Darurat</span>
                    <span class="fw-semibold text-end"><?= esc($penghuni['emergency_contact'] ?? '-') ?></span>
                </li>
                <li class="list-group-item">
                    <div class="text-secondary mb-1">Alamat Asal</div>
                    <div><?= esc($penghuni['address'] ?? '-') ?></div>
                </li>
                <li class="list-group-item">
                    <div class="text-secondary mb-1">Akun Login</div>
                    <?php if ($akun !== null): ?>
                        <div class="fw-semibold"><?= esc($akun['email']) ?></div>
                        <div class="text-success small"><i class="bi bi-check-circle me-1"></i>Tertaut &amp; bisa masuk portal</div>
                    <?php else: ?>
                        <span class="badge text-bg-light text-secondary border">Belum tertaut</span>
                    <?php endif; ?>
                </li>
                <li class="list-group-item d-flex justify-content-between gap-3">
                    <span class="text-secondary">Terdaftar</span>
                    <span class="text-end"><?= esc(tanggal_indo($penghuni['created_at'])) ?></span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Kolom kanan: keuangan & riwayat -->
    <div class="col-12 col-lg-8">
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-1"><i class="bi bi-receipt me-1"></i>Total Tagihan</div>
                        <div class="fs-5 fw-bold"><?= rupiah($ringkasan['tagihan']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-1"><i class="bi bi-cash-coin me-1"></i>Sudah Dibayar</div>
                        <div class="fs-5 fw-bold text-success"><?= rupiah($ringkasan['dibayar']) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-secondary small mb-1"><i class="bi bi-exclamation-circle me-1"></i>Sisa Tagihan</div>
                        <div class="fs-5 fw-bold <?= $ringkasan['sisa'] > 0 ? 'text-danger' : 'text-success' ?>">
                            <?= rupiah($ringkasan['sisa']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($sewaAktif !== null): ?>
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-house-door me-2"></i>Sewa Aktif
                </div>
                <div class="card-body">
                    <div class="row g-3 small">
                        <div class="col-6 col-md-3">
                            <div class="text-secondary">Kamar</div>
                            <div class="fw-semibold"><?= esc($sewaAktif['room_number']) ?> (Lantai <?= (int) $sewaAktif['floor'] ?>)</div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-secondary">Mulai</div>
                            <div class="fw-semibold"><?= esc(tanggal_indo($sewaAktif['start_date'])) ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-secondary">Harga Sepakat</div>
                            <div class="fw-semibold"><?= rupiah($sewaAktif['monthly_price']) ?></div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="text-secondary">Deposit</div>
                            <div class="fw-semibold"><?= rupiah($sewaAktif['deposit']) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white pb-0">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-tagihan" type="button" role="tab">
                            <i class="bi bi-receipt me-1"></i>Tagihan
                            <span class="badge text-bg-secondary ms-1"><?= count($tagihan) ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-pembayaran" type="button" role="tab">
                            <i class="bi bi-cash-coin me-1"></i>Pembayaran
                            <span class="badge text-bg-secondary ms-1"><?= count($pembayaran) ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-sewa" type="button" role="tab">
                            <i class="bi bi-clock-history me-1"></i>Riwayat Sewa
                            <span class="badge text-bg-secondary ms-1"><?= count($riwayatSewa) ?></span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="tab-content">
                <!-- Tagihan -->
                <div class="tab-pane fade show active" id="tab-tagihan" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Periode</th>
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
                                <tr><td colspan="7" class="text-center text-secondary py-4">Belum ada tagihan.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($tagihan as $t): ?>
                                <tr>
                                    <td class="fw-semibold"><?= esc(periode_indo($t['period_month'], $t['period_year'])) ?></td>
                                    <td><?= esc($t['room_number']) ?></td>
                                    <td class="text-end"><?= rupiah($t['amount']) ?></td>
                                    <td class="text-end text-success"><?= rupiah($t['dibayar']) ?></td>
                                    <td class="text-end <?= $t['sisa'] > 0 ? 'text-danger' : '' ?>"><?= rupiah($t['sisa']) ?></td>
                                    <td class="small"><?= esc(tanggal_indo($t['due_date'])) ?></td>
                                    <td class="text-center"><?= badge_tagihan($t['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pembayaran -->
                <div class="tab-pane fade" id="tab-pembayaran" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Untuk Periode</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-center">Metode</th>
                                    <th class="text-center">Bukti</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($pembayaran === []): ?>
                                <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada pembayaran.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($pembayaran as $b): ?>
                                <tr>
                                    <td class="text-nowrap"><?= esc(tanggal_indo($b['paid_at'])) ?></td>
                                    <td><?= esc(periode_indo($b['period_month'], $b['period_year'])) ?></td>
                                    <td class="text-end fw-semibold"><?= rupiah($b['amount']) ?></td>
                                    <td class="text-center">
                                        <span class="badge text-bg-<?= $b['method'] === 'transfer' ? 'info' : 'secondary' ?>">
                                            <?= esc(ucfirst($b['method'])) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (! empty($b['proof_file'])): ?>
                                            <a href="<?= esc(url_berkas('pembayaran', $b['proof_file']), 'attr') ?>"
                                               target="_blank" class="btn btn-sm btn-outline-secondary" title="Lihat bukti">
                                                <i class="bi bi-paperclip"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-secondary">&mdash;</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-secondary"><?= esc($b['note'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Riwayat sewa -->
                <div class="tab-pane fade" id="tab-sewa" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Kamar</th>
                                    <th>Mulai</th>
                                    <th>Selesai</th>
                                    <th class="text-end">Harga / Bulan</th>
                                    <th class="text-end">Deposit</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if ($riwayatSewa === []): ?>
                                <tr><td colspan="6" class="text-center text-secondary py-4">Belum ada riwayat sewa.</td></tr>
                            <?php endif; ?>
                            <?php foreach ($riwayatSewa as $s): ?>
                                <tr>
                                    <td class="fw-semibold"><?= esc($s['room_number']) ?></td>
                                    <td class="text-nowrap"><?= esc(tanggal_indo($s['start_date'])) ?></td>
                                    <td class="text-nowrap"><?= $s['end_date'] !== null ? esc(tanggal_indo($s['end_date'])) : '<span class="text-secondary">masih berjalan</span>' ?></td>
                                    <td class="text-end"><?= rupiah($s['monthly_price']) ?></td>
                                    <td class="text-end"><?= rupiah($s['deposit']) ?></td>
                                    <td class="text-center"><?= badge_sewa($s['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= view('partials/modal_hapus') ?>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<?= view('partials/script_hapus') ?>
<?php $this->endSection() ?>
