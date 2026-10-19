<?php
/**
 * Form tambah / ubah kamar.
 * Variabel: $kamar (null saat tambah), $aksi, $tombol, $sewaAktif
 */
$this->extend('layouts/admin');

$nilaiHarga = old('price', $kamar !== null ? number_format((float) $kamar['price'], 0, ',', '.') : '');
$opsiStatus = ['kosong' => 'Kosong', 'terisi' => 'Terisi', 'perbaikan' => 'Perbaikan'];
$statusTerpilih = old('status', $kamar['status'] ?? 'kosong');

if ($sewaAktif !== null) {
    $statusTerpilih = 'terisi';
}
?>

<?php $this->section('content') ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= base_url('kamar') ?>" class="text-decoration-none">Data Kamar</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= esc($title) ?></li>
    </ol>
</nav>

<?php if ($sewaAktif !== null): ?>
    <div class="alert alert-warning d-flex align-items-start">
        <i class="bi bi-info-circle-fill me-2 mt-1"></i>
        <div>
            Kamar ini sedang disewa oleh <strong><?= esc($sewaAktif['tenant_name']) ?></strong>
            sejak <?= esc(tanggal_indo($sewaAktif['start_date'])) ?>.
            Status otomatis <strong>Terisi</strong> dan hanya bisa berubah setelah proses check-out.
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-door-open me-2"></i><?= esc($title) ?>
            </div>
            <div class="card-body">
                <?= form_open($aksi) ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="room_number">Nomor Kamar</label>
                            <input type="text" name="room_number" id="room_number"
                                   class="form-control <?= session('errors.room_number') ? 'is-invalid' : '' ?>"
                                   value="<?= esc(old('room_number', $kamar['room_number'] ?? '')) ?>"
                                   placeholder="Contoh: A-01" required autofocus>
                            <?php if (session('errors.room_number')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.room_number')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="floor">Lantai</label>
                            <input type="number" name="floor" id="floor" min="0" max="50"
                                   class="form-control <?= session('errors.floor') ? 'is-invalid' : '' ?>"
                                   value="<?= esc(old('floor', $kamar['floor'] ?? '1')) ?>" required>
                            <?php if (session('errors.floor')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.floor')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="price">Harga Sewa per Bulan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="price" id="price" inputmode="numeric"
                                       class="form-control <?= session('errors.price') ? 'is-invalid' : '' ?>"
                                       value="<?= esc($nilaiHarga) ?>" placeholder="800.000" required>
                                <?php if (session('errors.price')): ?>
                                    <div class="invalid-feedback"><?= esc(session('errors.price')) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Boleh diketik dengan atau tanpa titik pemisah ribuan.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="status">Status Kamar</label>
                            <?php if ($sewaAktif !== null): ?>
                                <input type="text" class="form-control" value="Terisi" disabled>
                                <input type="hidden" name="status" value="terisi">
                            <?php else: ?>
                                <select name="status" id="status" class="form-select <?= session('errors.status') ? 'is-invalid' : '' ?>" required>
                                    <?php foreach ($opsiStatus as $nilai => $label): ?>
                                        <option value="<?= esc($nilai, 'attr') ?>" <?= $statusTerpilih === $nilai ? 'selected' : '' ?>>
                                            <?= esc($label) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if (session('errors.status')): ?>
                                    <div class="invalid-feedback"><?= esc(session('errors.status')) ?></div>
                                <?php endif; ?>
                                <div class="form-text">Pilih "Perbaikan" bila kamar sedang tidak bisa disewakan.</div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="facilities">Fasilitas</label>
                            <textarea name="facilities" id="facilities" rows="2" class="form-control"
                                      placeholder="AC, Kamar Mandi Dalam, WiFi, Kasur"><?= esc(old('facilities', $kamar['facilities'] ?? '')) ?></textarea>
                            <div class="form-text">Pisahkan dengan koma agar mudah dicari.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="notes">Catatan</label>
                            <textarea name="notes" id="notes" rows="2" class="form-control"
                                      placeholder="Contoh: dekat tangga, jendela menghadap selatan"><?= esc(old('notes', $kamar['notes'] ?? '')) ?></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i><?= esc($tombol) ?>
                        </button>
                        <a href="<?= base_url('kamar') ?>" class="btn btn-light">Batal</a>
                    </div>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-lightbulb me-2"></i>Tips Pengisian
            </div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item">Nomor kamar harus unik, misalnya <code>A-01</code>, <code>B-02</code>.</li>
                <li class="list-group-item">Harga sewa dipakai sebagai nilai awal saat check-in penghuni.</li>
                <li class="list-group-item">Status <strong>Terisi</strong> biasanya berubah otomatis saat ada check-in.</li>
                <li class="list-group-item">Kamar yang sudah punya riwayat sewa hanya dinonaktifkan (riwayat tetap tersimpan), sedangkan kamar yang belum pernah disewa dihapus permanen.</li>
            </ul>
        </div>
    </div>
</div>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
    // Rapikan tampilan angka saat fokus berpindah (titik sebagai pemisah ribuan).
    (function () {
        const input = document.getElementById('price');
        if (!input) {
            return;
        }

        const rapikan = function () {
            const angka = input.value.replace(/[^0-9]/g, '');
            input.value = angka === '' ? '' : new Intl.NumberFormat('id-ID').format(angka);
        };

        input.addEventListener('blur', rapikan);
    })();
</script>
<?php $this->endSection() ?>
