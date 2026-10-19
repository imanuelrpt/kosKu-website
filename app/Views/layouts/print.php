<?php
/**
 * Layout khusus halaman cetak (tagihan & kwitansi).
 * Tanpa sidebar/topbar, siap dicetak atau disimpan sebagai PDF.
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?= view('partials/head', ['title' => $title ?? 'Cetak']) ?>
    <style>
        body { background: #fff; }
        .print-paper { max-width: 800px; margin: 0 auto; }
        .print-area { border: 1px solid #dee2e6 !important; }
        .tabel-rincian td, .tabel-rincian th { padding: .5rem .75rem; }

        @media print {
            .no-print { display: none !important; }
            .print-paper { max-width: none; margin: 0; }
            .print-area { border: 0 !important; box-shadow: none !important; }
            .card-body { padding: 0 !important; }
            @page { margin: 12mm; }
        }
    </style>
</head>
<body class="p-3 p-lg-4">

<?= $this->renderSection('content') ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
