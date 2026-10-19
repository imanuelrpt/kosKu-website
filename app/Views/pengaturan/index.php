<?php
/**
 * Pengaturan aplikasi (admin).
 */
$this->extend('layouts/admin');
$aktif = setting_aktif('registration_enabled');
?>

<?php $this->section('content') ?>
<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-sliders me-2"></i>Pengaturan Umum</div>
            <div class="card-body">
                <?= form_open('pengaturan/simpan') ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label" for="app_name">Nama Aplikasi</label>
                            <input type="text" name="app_name" id="app_name" class="form-control"
                                   value="<?= esc(setting('app_name', 'KosKu')) ?>" maxlength="60">
                            <div class="form-text">Tampil pada judul halaman dan sidebar.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="kos_name">Nama Kos</label>
                            <input type="text" name="kos_name" id="kos_name" class="form-control"
                                   value="<?= esc(setting('kos_name', 'KosKu')) ?>" maxlength="80">
                            <div class="form-text">Tercetak pada tagihan dan kwitansi.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="kos_address">Alamat Kos</label>
                            <textarea name="kos_address" id="kos_address" class="form-control" rows="2"
                                      maxlength="255" placeholder="Jl. Contoh No. 10, Kota"><?= esc(setting('kos_address', '')) ?></textarea>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="kos_phone">Nomor Telepon Kos</label>
                            <input type="text" name="kos_phone" id="kos_phone" class="form-control"
                                   value="<?= esc(setting('kos_phone', '')) ?>" maxlength="30" placeholder="08xxxxxxxxxx">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="kos_owner">Nama Pemilik / Pengelola</label>
                            <input type="text" name="kos_owner" id="kos_owner" class="form-control"
                                   value="<?= esc(setting('kos_owner', '')) ?>" maxlength="80" placeholder="Nama pemilik kos">
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="kos_bank">Informasi Pembayaran / Rekening</label>
                            <input type="text" name="kos_bank" id="kos_bank" class="form-control"
                                   value="<?= esc(setting('kos_bank', '')) ?>" maxlength="150"
                                   placeholder="BCA 1234567890 a/n Budi Hartono">
                            <div class="form-text">Dicantumkan pada tagihan cetak sebagai tujuan transfer.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="invoice_due_day">Tanggal Jatuh Tempo Tagihan</label>
                            <div class="input-group">
                                <span class="input-group-text">Tanggal</span>
                                <input type="number" name="invoice_due_day" id="invoice_due_day" class="form-control"
                                       value="<?= (int) setting('invoice_due_day', 10) ?>" min="1" max="28">
                                <span class="input-group-text">bulan berikutnya</span>
                            </div>
                            <div class="form-text">
                                Tagihan periode ini jatuh tempo pada tanggal tersebut di bulan berikutnya.
                                Contoh: tagihan Oktober &rarr; jatuh tempo
                                <?= esc((int) setting('invoice_due_day', 10)) ?> November.
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <h3 class="h6 fw-semibold mb-2">Pendaftaran Penghuni</h3>
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" role="switch" name="registration_enabled"
                               id="registration_enabled" value="1" <?= $aktif ? 'checked' : '' ?>>
                        <label class="form-check-label" for="registration_enabled">
                            Aktifkan pendaftaran mandiri penghuni
                        </label>
                    </div>
                    <p class="text-secondary small mb-4">
                        Bila aktif, calon penghuni dapat membuat akun sendiri lewat halaman
                        <a href="<?= base_url('register') ?>" target="_blank">Daftar</a>.
                        Akun baru selalu berperan <strong>penghuni</strong> dan hanya bisa melihat data miliknya sendiri.
                    </p>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Pengaturan
                    </button>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Catatan</div>
            <ul class="list-group list-group-flush small">
                <li class="list-group-item">Data identitas kos dipakai pada halaman cetak tagihan dan kwitansi.</li>
                <li class="list-group-item">Perubahan tanggal jatuh tempo hanya berlaku untuk tagihan yang dibuat setelahnya.</li>
                <li class="list-group-item">Tagihan bulanan dibuat lewat menu Tagihan atau perintah <code>php spark invoices:generate</code>.</li>
            </ul>
        </div>
    </div>
</div>
<?php $this->endSection() ?>
