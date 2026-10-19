<?php
/**
 * Partial: pesan flash (sukses / gagal / info / warning).
 * Pemakaian: session()->setFlashdata('sukses', 'Data tersimpan');
 */
$flashMap = [
    'sukses'  => ['class' => 'success', 'icon' => 'check-circle-fill'],
    'gagal'   => ['class' => 'danger',  'icon' => 'exclamation-triangle-fill'],
    'info'    => ['class' => 'info',    'icon' => 'info-circle-fill'],
    'warning' => ['class' => 'warning', 'icon' => 'exclamation-circle-fill'],
];
?>
<?php foreach ($flashMap as $key => $meta): ?>
    <?php if (session()->getFlashdata($key)): ?>
        <div class="alert alert-<?= $meta['class'] ?> alert-dismissible fade show d-flex align-items-start" role="alert">
            <i class="bi bi-<?= $meta['icon'] ?> me-2 mt-1"></i>
            <div class="flex-grow-1"><?= esc(session()->getFlashdata($key)) ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    <?php endif; ?>
<?php endforeach; ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="fw-semibold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Periksa kembali isian Anda:</div>
        <ul class="mb-0 ps-3">
            <?php foreach ((array) session()->getFlashdata('errors') as $err): ?>
                <li><?= esc($err) ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
    </div>
<?php endif; ?>
