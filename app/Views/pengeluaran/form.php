<?php
/**
 * Form tambah / ubah pengeluaran.
 * Variabel: $aksi, $pengeluaran (null saat tambah)
 */
$this->extend('layouts/admin');

$baru      = $pengeluaran === null;
$kategori  = (string) old('category', $pengeluaran['category'] ?? '');
$jumlah    = (string) old('amount', $baru ? '' : rupiah($pengeluaran['amount'], false));
$tanggal   = (string) old('date', $baru ? date('Y-m-d') : tanggal_untuk_input($pengeluaran['date']));
$catatan   = (string) old('note', (string) ($pengeluaran['note'] ?? ''));

$opsiKategori = ['listrik' => 'Listrik', 'air' => 'Air', 'perbaikan' => 'Perbaikan', 'lainnya' => 'Lainnya'];
?>

<?php $this->section('content') ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= base_url('pengeluaran') ?>" class="text-decoration-none">Pengeluaran</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= $baru ? 'Tambah' : 'Ubah' ?></li>
    </ol>
</nav>

<h2 class="h5 mb-3"><?= $baru ? 'Tambah Pengeluaran' : 'Ubah Pengeluaran' ?></h2>

<?= form_open($aksi) ?>
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-wallet2 me-2"></i>Data Pengeluaran</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="category">Kategori</label>
                            <select name="category" id="category"
                                    class="form-select <?= session('errors.category') ? 'is-invalid' : '' ?>" required>
                                <option value="">&mdash; Pilih kategori &mdash;</option>
                                <?php foreach ($opsiKategori as $nilai => $label): ?>
                                    <option value="<?= esc($nilai, 'attr') ?>" <?= $kategori === $nilai ? 'selected' : '' ?>>
                                        <?= esc($label) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (session('errors.category')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.category')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="date">Tanggal</label>
                            <input type="date" name="date" id="date"
                                   class="form-control <?= session('errors.date') ? 'is-invalid' : '' ?>"
                                   value="<?= esc($tanggal) ?>" required>
                            <?php if (session('errors.date')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.date')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="amount">Jumlah</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="amount" id="amount" inputmode="numeric"
                                       class="form-control <?= session('errors.amount') ? 'is-invalid' : '' ?>"
                                       value="<?= esc($jumlah) ?>" placeholder="150.000" required>
                                <?php if (session('errors.amount')): ?>
                                    <div class="invalid-feedback"><?= esc(session('errors.amount')) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">Boleh ditulis dengan titik, contoh: 150.000</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="note">Catatan</label>
                            <textarea name="note" id="note" rows="3" class="form-control"
                                      maxlength="500" placeholder="Contoh: token listrik 3 kamar bulan ini"><?= esc($catatan) ?></textarea>
                            <?php if (session('errors.note')): ?>
                                <div class="invalid-feedback d-block"><?= esc(session('errors.note')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i><?= $baru ? 'Simpan' : 'Perbarui' ?>
                        </button>
                        <a href="<?= base_url('pengeluaran') ?>" class="btn btn-light">Batal</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Panduan Kategori</div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item"><strong>Listrik</strong> &mdash; token / tagihan listrik kos.</li>
                    <li class="list-group-item"><strong>Air</strong> &mdash; tagihan PDAM / pengisian air.</li>
                    <li class="list-group-item"><strong>Perbaikan</strong> &mdash; servis, material, tukang.</li>
                    <li class="list-group-item"><strong>Lainnya</strong> &mdash; kebersihan, internet, pajak, dsb.</li>
                </ul>
            </div>
        </div>
    </div>
<?= form_close() ?>
<?php $this->endSection() ?>
