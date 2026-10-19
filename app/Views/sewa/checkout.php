<?php
/**
 * Konfirmasi check-out penghuni.
 * Variabel: $aksi, $sewa, $tagihan (daftar tagihan belum lunas)
 */
$this->extend('layouts/admin');

$hariIni   = date('Y-m-d');
$tanggalKeluar = (string) old('end_date', $hariIni);

// Lama sewa dihitung sampai hari ini.
$mulai    = new DateTime((string) $sewa['start_date']);
$selesai  = new DateTime($hariIni);
$selisih  = $mulai->diff($selesai);
$lamaSewa = trim(
    ($selisih->y > 0 ? $selisih->y . ' tahun ' : '')
    . ($selisih->m > 0 ? $selisih->m . ' bulan ' : '')
    . $selisih->d . ' hari'
);

$totalTagihan = array_sum(array_map('floatval', array_column($tagihan, 'sisa')));
?>

<?php $this->section('content') ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= base_url('sewa') ?>" class="text-decoration-none">Sewa / Check-in</a></li>
        <li class="breadcrumb-item active" aria-current="page">Check-out Penghuni</li>
    </ol>
</nav>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <h2 class="h5 mb-0">Check-out Penghuni</h2>
    <a href="<?= base_url('sewa') ?>" class="btn btn-sm btn-light">
        <i class="bi bi-arrow-left me-1"></i>Kembali
    </a>
</div>

<?php if ($tagihan !== []): ?>
    <div class="alert alert-danger d-flex align-items-start">
        <i class="bi bi-exclamation-octagon-fill me-2 mt-1"></i>
        <div>
            Masih ada <strong><?= count($tagihan) ?> tagihan belum lunas</strong> dengan total sisa
            <strong><?= rupiah($totalTagihan) ?></strong>. Pelunasan bisa dicatat lebih dulu di menu Pembayaran
            atau tetap lanjutkan check-out di bawah ini.
        </div>
    </div>
<?php else: ?>
    <div class="alert alert-success d-flex align-items-start">
        <i class="bi bi-check-circle-fill me-2 mt-1"></i>
        <div>Semua tagihan penghuni ini sudah lunas. Check-out bisa langsung diproses.</div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-house-door me-2"></i>Ringkasan Sewa
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <span class="avatar-initial"><?= esc(inisial_nama($sewa['tenant_name'])) ?></span>
                    <div>
                        <div class="fw-semibold">
                            <a href="<?= base_url('penghuni/' . $sewa['tenant_id']) ?>" class="text-decoration-none">
                                <?= esc($sewa['tenant_name']) ?>
                            </a>
                        </div>
                        <div class="small text-secondary"><?= esc($sewa['tenant_phone'] ?? 'Tanpa nomor HP') ?></div>
                    </div>
                </div>

                <div class="row g-3 small">
                    <div class="col-6 col-md-3">
                        <div class="text-secondary">Kamar</div>
                        <div class="fw-semibold"><?= esc($sewa['room_number']) ?> (Lantai <?= (int) $sewa['floor'] ?>)</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-secondary">Mulai Menempati</div>
                        <div class="fw-semibold"><?= esc(tanggal_indo($sewa['start_date'])) ?></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-secondary">Lama Sewa</div>
                        <div class="fw-semibold"><?= esc($lamaSewa) ?></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-secondary">Status</div>
                        <div><?= badge_sewa($sewa['status']) ?></div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-secondary">Harga Sepakat</div>
                        <div class="fw-semibold"><?= rupiah($sewa['monthly_price']) ?> / bulan</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-secondary">Deposit</div>
                        <div class="fw-semibold"><?= rupiah($sewa['deposit']) ?></div>
                    </div>
                </div>

                <div class="form-text mt-3 mb-0">
                    Pengembalian deposit dicatat manual karena tidak termasuk dalam perhitungan tagihan bulanan.
                </div>
            </div>
        </div>

        <?= form_open($aksi) ?>
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-box-arrow-right me-2"></i>Data Check-out
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="end_date">Tanggal Keluar</label>
                            <input type="date" name="end_date" id="end_date"
                                   class="form-control <?= session('gagal') ? 'is-invalid' : '' ?>"
                                   value="<?= esc($tanggalKeluar) ?>"
                                   min="<?= esc((string) $sewa['start_date'], 'attr') ?>"
                                   max="<?= esc($hariIni, 'attr') ?>" required>
                            <div class="form-text">
                                Tidak boleh lebih awal dari tanggal masuk (<?= esc(tanggal_indo($sewa['start_date'])) ?>)
                                dan tidak melewati hari ini.
                            </div>
                        </div>
                    </div>

                    <?php if ($tagihan !== []): ?>
                        <div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" name="konfirmasi_tagihan"
                                   id="konfirmasi_tagihan" value="1" <?= old('konfirmasi_tagihan') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="konfirmasi_tagihan">
                                Saya mengerti masih ada tagihan belum lunas dan tetap melanjutkan check-out.
                            </label>
                        </div>
                    <?php endif; ?>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-box-arrow-right me-1"></i>Proses Check-out
                        </button>
                        <a href="<?= base_url('sewa') ?>" class="btn btn-light">Batal</a>
                    </div>
                </div>
            </div>
        <?= form_close() ?>
    </div>

    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-receipt me-2"></i>Tagihan Belum Lunas
                <span class="badge text-bg-secondary ms-1"><?= count($tagihan) ?></span>
            </div>
            <?php if ($tagihan === []): ?>
                <div class="card-body text-center text-secondary small py-4">
                    <i class="bi bi-check2-circle fs-4 d-block mb-2 text-success"></i>
                    Tidak ada tagihan tertunggak.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Periode</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-end">Sisa</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($tagihan as $t): ?>
                            <tr>
                                <td class="text-nowrap"><?= esc(periode_indo($t['period_month'], $t['period_year'])) ?></td>
                                <td class="text-end text-nowrap"><?= rupiah($t['amount']) ?></td>
                                <td class="text-end text-nowrap fw-semibold text-danger"><?= rupiah($t['sisa']) ?></td>
                                <td class="text-center"><?= badge_tagihan($t['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="2" class="text-end">Total Sisa</th>
                                <th class="text-end text-danger"><?= rupiah($totalTagihan) ?></th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-info-circle me-2"></i>Setelah Check-out
            </div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item">Sewa ditutup dengan status <strong>Selesai</strong> dan tanggal keluar tercatat.</li>
                <li class="list-group-item">Status kamar otomatis kembali <strong>Kosong</strong> sehingga siap disewakan lagi.</li>
                <li class="list-group-item">Riwayat sewa dan seluruh tagihannya tetap tersimpan di sistem.</li>
                <li class="list-group-item">Penghuni bisa langsung di-check-in ke kamar lain bila diperlukan.</li>
            </ul>
        </div>
    </div>
</div>
<?php $this->endSection() ?>
