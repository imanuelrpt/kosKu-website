<?php
/**
 * Partial: modal konfirmasi hapus (dipakai bersama).
 *
 * Tombol pemicu harus memuat atribut:
 *   data-bs-toggle="modal" data-bs-target="#modalHapus"
 *   data-hapus-url="<URL aksi POST hapus>"
 *   data-hapus-nama="<nama data yang akan dihapus>"
 *   data-hapus-catatan="<opsional, penjelasan tambahan>"
 */
?>
<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalHapusLabel">
                    <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Anda yakin ingin menghapus <strong id="hapusNama">data ini</strong>?</p>
                <p class="text-secondary small mb-0" id="hapusCatatan">Data yang dihapus tidak dapat dikembalikan.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                <form method="post" id="formHapus" action="#" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>Ya, Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
