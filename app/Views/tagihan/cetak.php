<?php
/**
 * Tagihan siap cetak (atau simpan sebagai PDF).
 * Variabel: $tagihan, $pembayaran
 * Opsional: $kembaliUrl, $kembaliLabel, $waUrl, $waLabel
 *           ($waUrl = false untuk menyembunyikan tombol WhatsApp)
 */
$this->extend('layouts/print');

$kembaliUrl   = $kembaliUrl ?? base_url('tagihan/' . $tagihan['id']);
$kembaliLabel = $kembaliLabel ?? 'Kembali ke Detail';
$waUrl        = $waUrl ?? wa_link($tagihan['tenant_phone'], pesan_wa_tagihan($tagihan));
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
                'judul' => 'Tagihan',
                'nomor' => nomor_invoice($tagihan),
            ]) ?>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="small text-secondary">Ditagihkan kepada</div>
                    <div class="fw-bold fs-6"><?= esc($tagihan['tenant_name']) ?></div>
                    <?php if (! empty($tagihan['tenant_address'])): ?>
                        <div class="small"><?= esc($tagihan['tenant_address']) ?></div>
                    <?php endif; ?>
                    <div class="small">
                        Kamar <strong><?= esc($tagihan['room_number']) ?></strong>
                        &middot; Lantai <?= (int) $tagihan['floor'] ?>
                    </div>
                    <div class="small">HP: <?= esc($tagihan['tenant_phone'] ?? '-') ?></div>
                </div>
                <div class="col-12 col-md-6">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-secondary w-50">Periode Tagihan</td>
                            <td class="fw-semibold text-end"><?= esc(periode_indo($tagihan['period_month'], $tagihan['period_year'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Tanggal Terbit</td>
                            <td class="fw-semibold text-end"><?= esc(tanggal_indo($tagihan['created_at'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Jatuh Tempo</td>
                            <td class="fw-semibold text-end"><?= esc(tanggal_indo($tagihan['due_date'])) ?></td>
                        </tr>
                        <tr>
                            <td class="text-secondary">Status</td>
                            <td class="text-end"><?= badge_tagihan($tagihan['status']) ?></td>
                        </tr>
                    </table>
                </div>
            </div>

            <table class="table tabel-rincian border">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px;">No</th>
                        <th>Keterangan</th>
                        <th class="text-end" style="width:180px;">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td>
                            Sewa kamar <strong><?= esc($tagihan['room_number']) ?></strong>
                            untuk periode <strong><?= esc(periode_indo($tagihan['period_month'], $tagihan['period_year'])) ?></strong>
                        </td>
                        <td class="text-end"><?= rupiah($tagihan['amount']) ?></td>
                    </tr>
                    <tr class="fw-bold">
                        <td colspan="2" class="text-end">Total Tagihan</td>
                        <td class="text-end"><?= rupiah($tagihan['amount']) ?></td>
                    </tr>
                    <tr>
                        <td colspan="2" class="text-end">Sudah Dibayar</td>
                        <td class="text-end"><?= rupiah($tagihan['dibayar']) ?></td>
                    </tr>
                    <tr class="fw-bold table-warning">
                        <td colspan="2" class="text-end">Sisa yang Harus Dibayar</td>
                        <td class="text-end"><?= rupiah($tagihan['sisa']) ?></td>
                    </tr>
                </tbody>
            </table>

            <p class="small mb-4">
                <span class="text-secondary">Terbilang:</span>
                <strong><?= esc(terbilang($tagihan['sisa'] > 0 ? $tagihan['sisa'] : $tagihan['amount'], true)) ?></strong>
            </p>

            <?php if ($pembayaran !== []): ?>
                <div class="mb-4">
                    <div class="fw-semibold border-bottom pb-1 mb-2">Riwayat Pembayaran</div>
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px;">No</th>
                                <th>Tanggal</th>
                                <th>Metode</th>
                                <th>Catatan</th>
                                <th class="text-end">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($pembayaran as $i => $b): ?>
                            <tr>
                                <td><?= $i + 1 ?></td>
                                <td><?= esc(tanggal_indo($b['paid_at'])) ?></td>
                                <td><?= esc($b['method'] === 'transfer' ? 'Transfer' : 'Tunai') ?></td>
                                <td class="small"><?= esc($b['note'] ?? '-') ?></td>
                                <td class="text-end"><?= rupiah($b['amount']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if (setting('kos_bank')): ?>
                <div class="alert alert-light border small mb-4">
                    <div class="fw-semibold mb-1"><i class="bi bi-bank me-1"></i>Pembayaran dapat ditransfer ke</div>
                    <?= esc(setting('kos_bank')) ?>
                </div>
            <?php endif; ?>

            <p class="small text-secondary mb-4">
                Mohon melakukan pembayaran sebelum tanggal jatuh tempo. Bila sudah membayar, bukti transfer
                dapat dikirimkan ke pengelola agar pembayaran segera dicatat. Terima kasih.
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
