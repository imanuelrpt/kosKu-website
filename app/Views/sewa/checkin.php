<?php
/**
 * Form check-in penghuni.
 * Variabel: $aksi, $kamarSiap, $penghuniSiap, $kamarTerpilih, $penghuniTerpilih, $hargaAwal
 */
$this->extend('layouts/admin');

$kamarDipilih    = (string) old('room_id', $kamarTerpilih > 0 ? $kamarTerpilih : '');
$penghuniDipilih = (string) old('tenant_id', $penghuniTerpilih > 0 ? $penghuniTerpilih : '');
$nilaiHarga      = (string) old('monthly_price', $hargaAwal);
$nilaiDeposit    = (string) old('deposit', '');
$tanggalMasuk    = (string) old('start_date', date('Y-m-d'));
?>

<?php $this->section('content') ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= base_url('sewa') ?>" class="text-decoration-none">Sewa / Check-in</a></li>
        <li class="breadcrumb-item active" aria-current="page">Check-in Penghuni</li>
    </ol>
</nav>

<h2 class="h5 mb-3">Check-in Penghuni</h2>

<?php if ($kamarSiap === []): ?>
    <div class="alert alert-warning d-flex align-items-start">
        <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
        <div>
            Tidak ada kamar yang siap disewa. Pastikan ada kamar berstatus <strong>Kosong</strong> dan
            tidak sedang ditempati. <a href="<?= base_url('kamar') ?>" class="alert-link">Kelola data kamar</a>.
        </div>
    </div>
<?php endif; ?>

<?php if ($penghuniSiap === []): ?>
    <div class="alert alert-warning d-flex align-items-start">
        <i class="bi bi-people-fill me-2 mt-1"></i>
        <div>
            Semua penghuni sudah punya sewa aktif. <a href="<?= base_url('penghuni/tambah') ?>" class="alert-link">Tambah
            penghuni baru</a> bila ada orang yang akan menyewa.
        </div>
    </div>
<?php endif; ?>

<?= form_open($aksi) ?>
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-key me-2"></i>Data Sewa
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="tenant_id">Penghuni</label>
                            <select name="tenant_id" id="tenant_id"
                                    class="form-select <?= session('errors.tenant_id') ? 'is-invalid' : '' ?>" required>
                                <option value="">&mdash; Pilih penghuni &mdash;</option>
                                <?php foreach ($penghuniSiap as $p): ?>
                                    <option value="<?= (int) $p['id'] ?>" <?= $penghuniDipilih === (string) $p['id'] ? 'selected' : '' ?>>
                                        <?= esc($p['name']) ?><?= $p['phone'] !== null && $p['phone'] !== '' ? ' — ' . esc($p['phone']) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (session('errors.tenant_id')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.tenant_id')) ?></div>
                            <?php endif; ?>
                            <div class="form-text">Hanya penghuni yang belum punya sewa aktif.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="room_id">Kamar</label>
                            <select name="room_id" id="room_id"
                                    class="form-select <?= session('errors.room_id') ? 'is-invalid' : '' ?>" required>
                                <option value="">&mdash; Pilih kamar &mdash;</option>
                                <?php foreach ($kamarSiap as $k): ?>
                                    <option value="<?= (int) $k['id'] ?>" data-harga="<?= (float) $k['price'] ?>"
                                            <?= $kamarDipilih === (string) $k['id'] ? 'selected' : '' ?>>
                                        <?= esc($k['room_number']) ?> &middot; Lantai <?= (int) $k['floor'] ?> &middot; <?= rupiah($k['price']) ?>/bulan
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (session('errors.room_id')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.room_id')) ?></div>
                            <?php endif; ?>
                            <div class="form-text">Hanya kamar berstatus Kosong yang tampil di sini.</div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label required" for="start_date">Tanggal Masuk</label>
                            <input type="date" name="start_date" id="start_date"
                                   class="form-control <?= session('errors.start_date') ? 'is-invalid' : '' ?>"
                                   value="<?= esc($tanggalMasuk) ?>" required>
                            <?php if (session('errors.start_date')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.start_date')) ?></div>
                            <?php endif; ?>
                            <div class="form-text">Tanggal penghuni mulai menempati kamar.</div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label required" for="monthly_price">Harga Sewa per Bulan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="monthly_price" id="monthly_price" inputmode="numeric"
                                       class="form-control <?= session('errors.monthly_price') ? 'is-invalid' : '' ?>"
                                       value="<?= esc($nilaiHarga) ?>" placeholder="800.000" required>
                                <?php if (session('errors.monthly_price')): ?>
                                    <div class="invalid-feedback"><?= esc(session('errors.monthly_price')) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Terisi otomatis dari harga kamar, boleh diubah sesuai kesepakatan.</div>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label" for="deposit">Deposit</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="deposit" id="deposit" inputmode="numeric"
                                       class="form-control <?= session('errors.deposit') ? 'is-invalid' : '' ?>"
                                       value="<?= esc($nilaiDeposit) ?>" placeholder="0">
                                <?php if (session('errors.deposit')): ?>
                                    <div class="invalid-feedback"><?= esc(session('errors.deposit')) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Opsional. Isi 0 atau kosongkan bila tanpa deposit.</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" <?= $kamarSiap === [] || $penghuniSiap === [] ? 'disabled' : '' ?>>
                            <i class="bi bi-box-arrow-in-right me-1"></i>Simpan Check-in
                        </button>
                        <a href="<?= base_url('sewa') ?>" class="btn btn-light">Batal</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-info-circle me-2"></i>Yang Terjadi Setelah Disimpan
                </div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item">Status kamar langsung berubah menjadi <strong>Terisi</strong>.</li>
                    <li class="list-group-item">Penghuni tercatat menempati kamar tersebut sampai dilakukan check-out.</li>
                    <li class="list-group-item">Satu penghuni hanya boleh punya satu sewa aktif.</li>
                    <li class="list-group-item">Tagihan bulanan dibuat dari menu Tagihan / perintah <code>invoices:generate</code>.</li>
                    <li class="list-group-item">Salah input? Check-in bisa dibatalkan dari daftar sewa selama belum ada tagihan.</li>
                </ul>
            </div>
        </div>
    </div>
<?= form_close() ?>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
    // Harga sewa mengikuti kamar yang dipilih (masih bisa diubah admin).
    (function () {
        const pilihKamar = document.getElementById('room_id');
        const harga = document.getElementById('monthly_price');

        if (!pilihKamar || !harga) {
            return;
        }

        pilihKamar.addEventListener('change', function () {
            const opsi = pilihKamar.options[pilihKamar.selectedIndex];
            const nilai = opsi ? opsi.getAttribute('data-harga') : null;

            if (!nilai) {
                return;
            }

            harga.value = new Intl.NumberFormat('id-ID').format(Number(nilai));
        });
    })();
</script>
<?php $this->endSection() ?>
