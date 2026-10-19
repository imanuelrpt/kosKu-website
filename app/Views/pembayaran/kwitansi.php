<?php
/**
 * Kwitansi pembayaran siap cetak.
 * Variabel: $pembayaran, $tagihan
 * Opsional: $kembaliUrl, $kembaliLabel, $waUrl, $waLabel
 *           ($waUrl = false untuk menyembunyikan tombol WhatsApp)
 */
$this->extend('layouts/print');

$kembaliUrl   = $kembaliUrl ?? base_url('pembayaran');
$kembaliLabel = $kembaliLabel ?? 'Kembali';
$waUrl        = $waUrl ?? wa_link($pembayaran['tenant_phone'], pesan_wa_kwitansi($pembayaran));
$waLabel      = $waLabel ?? 'Kirim ke WhatsApp';
?>

<?php $this->section('content') ?>
<div class="print-paper">

    <div class="no-print d-flex flex-wrap gap-2 mb-3">
        <button type="button" class="btn btn-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i>Cetak / Simpan PDF
        </button>
        <a href="<?= esc($kembaliUrl, 'attr') ?>" class="btn btn-light btn-sm">
            <i class="bi bi-arrow-left me-1"></i><?= esc($kembaliLabel) ?>
        </a>
        <?php if (! empty($waUrl)): ?>
            <a href="<?= esc($waUrl, 'attr') ?>" target="_blank" rel="noopener" class="btn btn-success btn-sm">
                <i class="bi bi-whatsapp me-1"></i><?= esc($waLabel) ?>
            </a>
        <?php endif; ?>
    </div>

    <div class="card print-area shadow-sm">
        <div class="card-body p-4">

            <?= view('partials/kop_cetak', [
                'judul' => 'Kwitansi',
                'nomor' => nomor_kwitansi($pembayaran),
            ]) ?>

            <table class="table table-borderless table-sm mb-4">
                <tr>
                    <td class="text-secondary" style="width:34%;">Sudah Terima Dari</td>
                    <td class="fw-bold">: <?= esc($pembayaran['tenant_name']) ?></td>
                </tr>
                <tr>
                    <td class="text-secondary">Kamar</td>
                    <td>: <?= esc($pembayaran['room_number']) ?> (Lantai <?= (int) $pembayaran['floor'] ?>)</td>
                </tr>
                <tr>
                    <td class="text-secondary">Uang Sejumlah</td>
                    <td class="fw-bold fs-6">: <?= rupiah($pembayaran['amount']) ?></td>
                </tr>
                <tr>
                    <td class="text-secondary">Terbilang</td>
                    <td>: <em><?= esc(terbilang($pembayaran['amount'], true)) ?></em></td>
                </tr>
                <tr>
                    <td class="text-secondary">Untuk Pembayaran</td>
                    <td>
                        : Tagihan sewa kamar <?= esc($pembayaran['room_number']) ?>
                        periode <?= esc(periode_indo($pembayaran['period_month'], $pembayaran['period_year'])) ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-secondary">Tanggal Pembayaran</td>
                    <td>: <?= esc(tanggal_indo($pembayaran['paid_at'])) ?></td>
                </tr>
                <tr>
                    <td class="text-secondary">Metode</td>
                    <td>: <?= esc($pembayaran['method'] === 'transfer' ? 'Transfer' : 'Tunai') ?></td>
                </tr>
                <?php if (! empty($pembayaran['note'])): ?>
                    <tr>
                        <td class="text-secondary">Catatan</td>
                        <td>: <?= esc($pembayaran['note']) ?></td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td class="text-secondary">Jatuh Tempo Tagihan</td>
                    <td>: <?= esc(tanggal_indo($pembayaran['invoice_due'])) ?></td>
                </tr>
            </table>

            <?php if ($tagihan !== null): ?>
                <table class="table table-sm tabel-rincian border mb-4">
                    <thead class="table-light">
                        <tr>
                            <th>Keterangan Tagihan</th>
                            <th class="text-end" style="width:200px;">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Jumlah tagihan periode <?= esc(periode_indo($tagihan['period_month'], $tagihan['period_year'])) ?></td>
                            <td class="text-end"><?= rupiah($tagihan['amount']) ?></td>
                        </tr>
                        <tr>
                            <td>
                                Sudah dibayar
                                <?php if ($tagihan['sisa'] <= 0): ?>
                                    <span class="badge text-bg-success ms-1">Lunas</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end"><?= rupiah($tagihan['dibayar']) ?></td>
                        </tr>
                        <tr class="fw-bold">
                            <td>Sisa tagihan</td>
                            <td class="text-end"><?= rupiah($tagihan['sisa']) ?></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (! empty($pembayaran['proof_file'])): ?>
                <p class="small mb-4">
                    <span class="text-secondary">Bukti transfer:</span>
                    <?= esc($pembayaran['proof_file']) ?>
                </p>
            <?php endif; ?>

            <p class="small text-secondary mb-4">
                Kwitansi ini dicetak otomatis oleh sistem dan sah sebagai bukti pembayaran.
            </p>

            <div class="row mt-5">
                <div class="col-6"></div>
                <div class="col-6 text-center">
                    <div>Penerima,</div>
                    <div style="height:64px;"></div>
                    <div class="fw-semibold border-top d-inline-block px-4 pt-1">
                        <?= esc(setting('kos_owner', 'Pengelola')) ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<?php $this->endSection() ?>
