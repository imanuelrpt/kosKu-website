<?php
/**
 * Form pencatatan pembayaran (mendukung cicilan).
 * Variabel: $aksi, $tagihan (boleh null), $daftarTagihan
 */
$this->extend('layouts/admin');

$idTerpilih = (string) old('invoice_id', $tagihan['id'] ?? '');
$nilaiBayar = (string) old('amount', $tagihan ? rupiah($tagihan['sisa'], false) : '');
$metodeAwal = (string) old('method', 'tunai');
$tanggalAwal = (string) old('paid_at', date('Y-m-d'));
?>

<?php $this->section('content') ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= base_url('pembayaran') ?>" class="text-decoration-none">Pembayaran</a></li>
        <li class="breadcrumb-item active" aria-current="page">Catat Pembayaran</li>
    </ol>
</nav>

<h2 class="h5 mb-3">Catat Pembayaran</h2>

<?php if ($daftarTagihan === []): ?>
    <div class="alert alert-warning d-flex align-items-start">
        <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
        <div>
            Tidak ada tagihan yang belum lunas. Semua tagihan sudah dibayar atau belum ada tagihan dibuat.
            <a href="<?= base_url('tagihan') ?>" class="alert-link">Lihat daftar tagihan</a>.
        </div>
    </div>
<?php endif; ?>

<?= form_open_multipart($aksi) ?>
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-cash-coin me-2"></i>Data Pembayaran</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label required" for="invoice_id">Tagihan</label>
                            <select name="invoice_id" id="invoice_id"
                                    class="form-select <?= session('errors.invoice_id') ? 'is-invalid' : '' ?>" required>
                                <option value="">&mdash; Pilih tagihan yang dibayar &mdash;</option>
                                <?php foreach ($daftarTagihan as $t): ?>
                                    <option value="<?= (int) $t['id'] ?>"
                                            data-sisa="<?= (float) $t['sisa'] ?>"
                                            data-jumlah="<?= (float) $t['amount'] ?>"
                                            data-dibayar="<?= (float) $t['amount'] - (float) $t['sisa'] ?>"
                                            <?= $idTerpilih === (string) $t['id'] ? 'selected' : '' ?>>
                                        <?= esc($t['room_number'] . ' - ' . $t['tenant_name'] . ' - ' . periode_indo($t['period_month'], $t['period_year']) . ' - sisa ' . rupiah($t['sisa'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (session('errors.invoice_id')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.invoice_id')) ?></div>
                            <?php endif; ?>
                            <div class="form-text">Hanya tagihan yang belum lunas yang muncul di daftar ini.</div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="amount">Jumlah Bayar</label>
                            <div class="input-group">
                                <span class="input-group-text">Rp</span>
                                <input type="text" name="amount" id="amount" inputmode="numeric"
                                       class="form-control <?= session('errors.amount') ? 'is-invalid' : '' ?>"
                                       value="<?= esc($nilaiBayar) ?>" placeholder="800.000" required>
                                <?php if (session('errors.amount')): ?>
                                    <div class="invalid-feedback"><?= esc(session('errors.amount')) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="form-text">
                                Boleh kurang dari sisa tagihan (cicilan).
                                <button type="button" class="btn btn-link btn-sm p-0 align-baseline" id="btnLunasi">
                                    Lunasi penuh
                                </button>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="paid_at">Tanggal Bayar</label>
                            <input type="date" name="paid_at" id="paid_at"
                                   class="form-control <?= session('errors.paid_at') ? 'is-invalid' : '' ?>"
                                   value="<?= esc($tanggalAwal) ?>" required>
                            <?php if (session('errors.paid_at')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.paid_at')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="method">Metode Pembayaran</label>
                            <select name="method" id="method"
                                    class="form-select <?= session('errors.method') ? 'is-invalid' : '' ?>">
                                <option value="tunai" <?= $metodeAwal === 'tunai' ? 'selected' : '' ?>>Tunai</option>
                                <option value="transfer" <?= $metodeAwal === 'transfer' ? 'selected' : '' ?>>Transfer</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6" id="kotakBukti">
                            <label class="form-label" for="proof_file">Bukti Transfer (opsional)</label>
                            <input type="file" name="proof_file" id="proof_file" class="form-control"
                                   accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">JPG, PNG, atau PDF. Maksimal 2 MB.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="note">Catatan</label>
                            <textarea name="note" id="note" rows="2" class="form-control"
                                      maxlength="500" placeholder="Contoh: cicilan pertama"><?= esc((string) old('note')) ?></textarea>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" <?= $daftarTagihan === [] ? 'disabled' : '' ?>>
                            <i class="bi bi-save me-1"></i>Simpan Pembayaran
                        </button>
                        <a href="<?= base_url('pembayaran') ?>" class="btn btn-light">Batal</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4" id="kartuRingkas">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-clipboard-data me-2"></i>Ringkasan Tagihan</div>
                <div class="card-body small">
                    <?php if ($tagihan !== null): ?>
                        <div class="fw-semibold mb-1">
                            <?= esc($tagihan['tenant_name']) ?> &middot; <?= esc($tagihan['room_number']) ?>
                        </div>
                        <div class="text-secondary mb-3">
                            <?= esc(periode_indo($tagihan['period_month'], $tagihan['period_year'])) ?>
                        </div>
                    <?php else: ?>
                        <div class="text-secondary mb-3" id="ringkasKosong">Pilih tagihan untuk melihat ringkasannya.</div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-secondary">Jumlah Tagihan</span>
                        <span id="ringkasJumlah" class="fw-semibold">
                            <?= $tagihan ? rupiah($tagihan['amount']) : '-' ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-secondary">Sudah Dibayar</span>
                        <span id="ringkasDibayar" class="fw-semibold text-success">
                            <?= $tagihan ? rupiah($tagihan['dibayar']) : '-' ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between py-1">
                        <span class="text-secondary">Sisa Tagihan</span>
                        <span id="ringkasSisa" class="fw-semibold text-danger">
                            <?= $tagihan ? rupiah($tagihan['sisa']) : '-' ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-2"></i>Catatan</div>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item">Jumlah bayar tidak boleh melebihi sisa tagihan.</li>
                    <li class="list-group-item">Status tagihan otomatis menjadi <strong>Lunas</strong> bila total pembayaran sudah mencukupi.</li>
                    <li class="list-group-item">Bukti transfer hanya perlu diunggah untuk pembayaran non-tunai.</li>
                    <li class="list-group-item">Setiap pembayaran dapat dicetak kwitansinya.</li>
                </ul>
            </div>
        </div>
    </div>
<?= form_close() ?>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
    (function () {
        const pilihTagihan = document.getElementById('invoice_id');
        const jumlah       = document.getElementById('amount');
        const metode       = document.getElementById('method');
        const kotakBukti   = document.getElementById('kotakBukti');
        const btnLunasi    = document.getElementById('btnLunasi');

        const ringkasJumlah  = document.getElementById('ringkasJumlah');
        const ringkasDibayar = document.getElementById('ringkasDibayar');
        const ringkasSisa    = document.getElementById('ringkasSisa');
        const ringkasKosong  = document.getElementById('ringkasKosong');

        const format = (angka) => 'Rp ' + new Intl.NumberFormat('id-ID').format(angka);

        function sembunyikanBukti() {
            const transfer = metode && metode.value === 'transfer';
            if (kotakBukti) {
                kotakBukti.classList.toggle('d-none', !transfer);
            }
        }

        function perbaruiRingkasan() {
            if (!pilihTagihan) {
                return;
            }

            const opsi = pilihTagihan.options[pilihTagihan.selectedIndex];

            if (!opsi || !opsi.value) {
                if (ringkasKosong) { ringkasKosong.classList.remove('d-none'); }
                if (ringkasJumlah) { ringkasJumlah.textContent = '-'; }
                if (ringkasDibayar) { ringkasDibayar.textContent = '-'; }
                if (ringkasSisa) { ringkasSisa.textContent = '-'; }
                return;
            }

            if (ringkasKosong) { ringkasKosong.classList.add('d-none'); }

            const total   = parseFloat(opsi.getAttribute('data-jumlah') || '0');
            const dibayar = parseFloat(opsi.getAttribute('data-dibayar') || '0');
            const sisa    = parseFloat(opsi.getAttribute('data-sisa') || '0');

            if (ringkasJumlah) { ringkasJumlah.textContent = format(total); }
            if (ringkasDibayar) { ringkasDibayar.textContent = format(dibayar); }
            if (ringkasSisa) { ringkasSisa.textContent = format(sisa); }
        }

        function isiJumlahDariSisa() {
            const opsi = pilihTagihan ? pilihTagihan.options[pilihTagihan.selectedIndex] : null;
            if (opsi && opsi.value && jumlah) {
                jumlah.value = new Intl.NumberFormat('id-ID').format(parseFloat(opsi.getAttribute('data-sisa') || '0'));
            }
        }

        if (pilihTagihan) {
            pilihTagihan.addEventListener('change', function () {
                perbaruiRingkasan();
                isiJumlahDariSisa();
            });
            perbaruiRingkasan();
        }

        if (metode) {
            metode.addEventListener('change', sembunyikanBukti);
            sembunyikanBukti();
        }

        if (btnLunasi) {
            btnLunasi.addEventListener('click', isiJumlahDariSisa);
        }
    })();
</script>
<?php $this->endSection() ?>
