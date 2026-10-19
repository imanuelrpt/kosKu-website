<?php
/**
 * Partial: skrip untuk modal konfirmasi hapus.
 * Sisipkan di dalam section "scripts" halaman.
 */
?>
<script>
    // Isi modal konfirmasi hapus sesuai tombol yang diklik.
    (function () {
        const modal = document.getElementById('modalHapus');
        if (!modal) {
            return;
        }

        modal.addEventListener('show.bs.modal', function (event) {
            const pemicu = event.relatedTarget;
            if (!pemicu) {
                return;
            }

            document.getElementById('formHapus').setAttribute('action', pemicu.getAttribute('data-hapus-url') || '#');
            document.getElementById('hapusNama').textContent = pemicu.getAttribute('data-hapus-nama') || 'data ini';
            document.getElementById('hapusCatatan').textContent =
                pemicu.getAttribute('data-hapus-catatan') || 'Data yang dihapus tidak dapat dikembalikan.';
        });
    })();
</script>
