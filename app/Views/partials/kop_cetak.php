<?php
/**
 * Partial: kop dokumen cetak (tagihan / kwitansi).
 * Variabel: $judul (mis. "TAGIHAN"), $nomor (nomor dokumen)
 */
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 border-bottom border-2 border-dark pb-3 mb-4">
    <div>
        <div class="fs-4 fw-bold"><?= esc(setting('kos_name', 'KosKu')) ?></div>
        <?php if (setting('kos_address')): ?>
            <div class="small"><?= esc(setting('kos_address')) ?></div>
        <?php endif; ?>
        <?php if (setting('kos_phone')): ?>
            <div class="small">Telp / WA: <?= esc(setting('kos_phone')) ?></div>
        <?php endif; ?>
        <?php if (setting('kos_owner')): ?>
            <div class="small">Pengelola: <?= esc(setting('kos_owner')) ?></div>
        <?php endif; ?>
    </div>
    <div class="text-end">
        <div class="fs-5 fw-bold text-uppercase"><?= esc($judul ?? 'Dokumen') ?></div>
        <div class="small">No. <strong><?= esc($nomor ?? '-') ?></strong></div>
        <div class="small text-secondary">Dicetak <?= esc(tanggal_indo(date('Y-m-d'))) ?></div>
    </div>
</div>
