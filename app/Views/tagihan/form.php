<?php
/**
 * Form buat tagihan manual.
 * Variabel: $aksi, $sewa (daftar sewa aktif), $dueDay
 */
$this->extend('layouts/admin');

$sewaDipilih = (string) old('occupancy_id', $sewa === [] ? '' : $sewa[0]['id']);
$bulanAwal   = (int) old('period_month', date('n'));
$tahunAwal   = (int) old('period_year', date('Y'));

// Jatuh tempo bawaan: tanggal $dueDay pada bulan setelah periode.
$bulanDepan = date('Y-m', strtotime(sprintf('%04d-%02d-01 +1 month', $tahunAwal, $bulanAwal)));
$jatuhAwal  = old('due_date', $bulanDepan . '-' . str_pad((string) $dueDay, 2, '0', STR_PAD_LEFT));

$nilaiJumlah = (string) old('amount', '');
if ($nilaiJumlah === '') {
    foreach ($sewa as $s) {
        if ((string) $s['id'] === $sewaDipilih) {
            $nilaiJumlah = rupiah($s['monthly_price'], false);
            break;
        }
    }
}
?>

<?php $this->section('content') ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= base_url('tagihan') ?>" class="text-decoration-none">Tagihan</a></li>
        <li class="breadcrumb-item active" aria-current="page">Buat Tagihan</li>
    </ol>
</nav>

<h2 class="h5 mb-3">Buat Tagihan Manual</h2>

<?php if ($sewa === []): ?>
    <div class="alert alert-warning d-flex align-items-start">
        <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
        <div>
            Belum ada sewa aktif, jadi tagihan manual belum bisa dibuat. Lakukan
            <a href="<?= base_url('sewa/checkin') ?>" class="alert-link">check-in penghuni</a> lebih dulu.
        </div>
    </div>
<?php endif; ?>

<?= form_open($aksi) ?>
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-receipt me-2"></i>Data Tagihan</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label required" for="occupancy_id">Sewa / Penghuni</label>
                            <select name="occupancy_id" id="occupancy_id"
                                    class="form-select <?= session('errors.occupancy_id') ? 'is-invalid' : '' ?>" required>
                                <option value="">&mdash; Pilih penghuni &mdash;</option>
                                <?php foreach ($sewa as $s): ?>
                                    <option value="<?= (int) $s['id'] ?>"
                                            data-harga="<?= (float) $s['monthly_price'] ?>"
                                            <?= $sewaDipilih === (string) $s['id'] ? 'selected' : '' ?>>
                                        <?= esc($s['room_number']) ?> &middot; <?= esc($s['tenant_name']) ?> &middot; <?= rupiah($s['monthly_price']) ?>/bulan
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (session('errors.occupancy_id')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.occupancy_id')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label required" for="period_month">Bulan</label>
                            <select name="period_month" id="period_month"
                                    class="form-select <?= session('errors.period_month') ? 'is-invalid' : '' ?>">
                                <?php for ($b = 1; $b <= 12; $b++): ?>
                                    <option value="<?= $b ?>" <?= $bulanAwal === $b ? 'selected' : '' ?>><?= esc(bulan_indo($b)) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="col-6 col-md-3">
                            <label class="form-label required" for="period_year">Tahun</label>
                            <input type="number" name="period_year" id="period_year"
                                   class="form-control <?= session('errors.period_year') ? 'is-invalid' : '' ?>"
                                   value="<?= $tahunAwal ?>" min="2000" max="2100" required>
                            <?php if (session('errors.period_year')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.period_year')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="amount">Jumlah Tagihan</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="amount" id="amount" inputmode="numeric"
                                       class="form-control <?= session('errors.amount') ? 'is-invalid' : '' ?>"
                                       value="<?= esc($nilaiJumlah) ?>" placeholder="800.000" required>
                                <?php if (session('errors.amount')): ?>
                                    <div class="invalid-feedback"><?= esc(session('errors.amount')) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Terisi otomatis dari harga sewa, boleh disesuaikan.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="due_date">Jatuh Tempo</label>
                            <input type="date" name="due_date" id="due_date"
                                   class="form-control <?= session('errors.due_date') ? 'is-invalid' : '' ?>"
                                   value="<?= esc($jatuhAwal) ?>" required>
                            <?php if (session('errors.due_date')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.due_date')) ?></div>
                            <?php endif; ?>
                            <div class="form-text">Bawaan: tanggal <?= (int) $dueDay ?> bulan berikutnya.</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" <?= $sewa === [] ? 'disabled' : '' ?>>
                            <i class="bi bi-save me-1"></i>Simpan Tagihan
                        </button>
                        <a href="<?= base_url('tagihan') ?>" class="btn btn-light">Batal</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Catatan</div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item">Satu periode hanya boleh punya satu tagihan per sewa.</li>
                    <li class="list-group-item">Untuk membuat tagihan semua penghuni sekaligus, pakai tombol <strong>Buat Otomatis</strong> di halaman Tagihan.</li>
                    <li class="list-group-item">Status awal <strong>Belum Bayar</strong>; otomatis menjadi <strong>Terlambat</strong> setelah lewat jatuh tempo.</li>
                    <li class="list-group-item">Pembayaran dicatat lewat menu <strong>Pembayaran</strong> (boleh cicilan).</li>
                </ul>
            </div>
        </div>
    </div>
<?= form_close() ?>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
    // Jumlah & jatuh tempo mengikuti pilihan sewa dan periode.
    (function () {
        const pilihSewa  = document.getElementById('occupancy_id');
        const jumlah     = document.getElementById('amount');
        const bulan      = document.getElementById('period_month');
        const tahun      = document.getElementById('period_year');
        const jatuhTempo = document.getElementById('due_date');
        const hariJatuh  = <?= (int) $dueDay ?>;

        if (pilihSewa && jumlah) {
            pilihSewa.addEventListener('change', function () {
                const opsi = pilihSewa.options[pilihSewa.selectedIndex];
                const nilai = opsi ? opsi.getAttribute('data-harga') : null;

                if (nilai) {
                    jumlah.value = new Intl.NumberFormat('id-ID').format(Number(nilai));
                }
            });
        }

        // Jatuh tempo dihitung ulang selama belum diubah manual.
        let otomatis = true;

        if (jatuhTempo) {
            jatuhTempo.addEventListener('input', function () {
                otomatis = false;
            });
        }

        function hitungJatuhTempo() {
            if (! otomatis || ! jatuhTempo || ! bulan || ! tahun) {
                return;
            }

            const b = parseInt(bulan.value, 10);
            const t = parseInt(tahun.value, 10);

            if (! b || ! t) {
                return;
            }

            // Bulan berikutnya (indeks bulan JS dimulai dari 0).
            const tanggal = new Date(t, b, hariJatuh);
            const teks = tanggal.getFullYear() + '-' +
                String(tanggal.getMonth() + 1).padStart(2, '0') + '-' +
                String(tanggal.getDate()).padStart(2, '0');

            jatuhTempo.value = teks;
        }

        if (bulan) { bulan.addEventListener('change', hitungJatuhTempo); }
        if (tahun) { tahun.addEventListener('input', hitungJatuhTempo); }
    })();
</script>
<?php $this->endSection() ?>
