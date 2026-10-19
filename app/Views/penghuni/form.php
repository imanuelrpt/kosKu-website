<?php
/**
 * Form tambah / ubah penghuni.
 * Variabel: $penghuni (null saat tambah), $aksi, $tombol, $akun, $kamarAktif
 */
$this->extend('layouts/admin');

$idAkunTerpilih = (string) old('user_id', $penghuni['user_id'] ?? '');
$fotoLama       = $penghuni['photo'] ?? null;
?>

<?php $this->section('content') ?>

<nav aria-label="breadcrumb">
    <ol class="breadcrumb small">
        <li class="breadcrumb-item"><a href="<?= base_url('penghuni') ?>" class="text-decoration-none">Data Penghuni</a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= esc($title) ?></li>
    </ol>
</nav>

<?php if ($kamarAktif !== null): ?>
    <div class="alert alert-info d-flex align-items-start">
        <i class="bi bi-house-door-fill me-2 mt-1"></i>
        <div>
            Sedang menempati <strong>Kamar <?= esc($kamarAktif['room_number']) ?></strong>
            sejak <?= esc(tanggal_indo($kamarAktif['start_date'])) ?>.
            Perubahan kamar dilakukan lewat menu <strong>Sewa / Check-in</strong>.
        </div>
    </div>
<?php endif; ?>

<?= form_open_multipart($aksi) ?>
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="bi bi-person-vcard me-2"></i>Data Penghuni
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="name">Nama Lengkap</label>
                            <input type="text" name="name" id="name"
                                   class="form-control <?= session('errors.name') ? 'is-invalid' : '' ?>"
                                   value="<?= esc(old('name', $penghuni['name'] ?? '')) ?>"
                                   placeholder="Nama sesuai KTP" required autofocus>
                            <?php if (session('errors.name')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.name')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="phone">Nomor HP</label>
                            <input type="text" name="phone" id="phone"
                                   class="form-control <?= session('errors.phone') ? 'is-invalid' : '' ?>"
                                   value="<?= esc(old('phone', $penghuni['phone'] ?? '')) ?>"
                                   placeholder="08xx-xxxx-xxxx">
                            <?php if (session('errors.phone')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.phone')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="id_number">Nomor KTP</label>
                            <input type="text" name="id_number" id="id_number"
                                   class="form-control <?= session('errors.id_number') ? 'is-invalid' : '' ?>"
                                   value="<?= esc(old('id_number', $penghuni['id_number'] ?? '')) ?>"
                                   placeholder="16 digit">
                            <?php if (session('errors.id_number')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.id_number')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="emergency_contact">Kontak Darurat</label>
                            <input type="text" name="emergency_contact" id="emergency_contact"
                                   class="form-control <?= session('errors.emergency_contact') ? 'is-invalid' : '' ?>"
                                   value="<?= esc(old('emergency_contact', $penghuni['emergency_contact'] ?? '')) ?>"
                                   placeholder="Nama - 08xx-xxxx-xxxx">
                            <?php if (session('errors.emergency_contact')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.emergency_contact')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="address">Alamat Asal</label>
                            <textarea name="address" id="address" rows="2"
                                      class="form-control <?= session('errors.address') ? 'is-invalid' : '' ?>"
                                      placeholder="Alamat lengkap sesuai KTP"><?= esc(old('address', $penghuni['address'] ?? '')) ?></textarea>
                            <?php if (session('errors.address')): ?>
                                <div class="invalid-feedback"><?= esc(session('errors.address')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12">
                            <label class="form-label" for="user_id">Akun Login Penghuni</label>
                            <select name="user_id" id="user_id" class="form-select <?= session('errors.user_id') ? 'is-invalid' : '' ?>">
                                <option value="">&mdash; Tanpa akun login &mdash;</option>
                                <?php foreach ($akun as $a): ?>
                                    <option value="<?= (int) $a['id'] ?>" <?= $idAkunTerpilih === (string) $a['id'] ? 'selected' : '' ?>>
                                        <?= esc($a['name']) ?> &lt;<?= esc($a['email']) ?>&gt;
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                Tautkan ke akun agar penghuni bisa masuk ke portal dan melihat tagihannya sendiri.
                                Pilihan ini hanya menampilkan akun berperan <strong>penghuni</strong> yang belum dipakai.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold"><i class="bi bi-image me-2"></i>Foto Penghuni</div>
                <div class="card-body text-center">
                    <?php if ($fotoLama !== null): ?>
                        <img id="pratinjau" src="<?= esc(url_berkas('penghuni', $fotoLama), 'attr') ?>"
                             alt="Foto penghuni" class="rounded mb-3 border" style="width:150px;height:150px;object-fit:cover;">
                    <?php else: ?>
                        <img id="pratinjau" src="" alt="" class="rounded mb-3 border d-none" style="width:150px;height:150px;object-fit:cover;">
                        <div id="placeholder" class="text-secondary mb-3">
                            <i class="bi bi-person-bounding-box fs-1 d-block"></i>
                            <span class="small">Belum ada foto</span>
                        </div>
                    <?php endif; ?>

                    <input type="file" name="photo" id="photo" class="form-control form-control-sm" accept="image/jpeg,image/png">
                    <div class="form-text text-start mt-2">
                        Format JPG atau PNG, maksimal 2 MB.
                    </div>

                    <?php if ($fotoLama !== null): ?>
                        <div class="form-check form-switch text-start mt-3">
                            <input class="form-check-input" type="checkbox" role="switch" name="hapus_foto"
                                   id="hapus_foto" value="1">
                            <label class="form-check-label small" for="hapus_foto">Hapus foto saat ini</label>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i><?= esc($tombol) ?>
                    </button>
                    <a href="<?= base_url('penghuni') ?>" class="btn btn-light">Batal</a>
                </div>
            </div>
        </div>
    </div>
<?= form_close() ?>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
    // Pratinjau foto sebelum diunggah.
    (function () {
        const input = document.getElementById('photo');
        const gambar = document.getElementById('pratinjau');
        const placeholder = document.getElementById('placeholder');

        if (!input || !gambar) {
            return;
        }

        input.addEventListener('change', function () {
            const berkas = input.files && input.files[0];
            if (!berkas) {
                return;
            }

            if (berkas.size > 2 * 1024 * 1024) {
                alert('Ukuran foto melebihi 2 MB. Silakan pilih berkas yang lebih kecil.');
                input.value = '';
                return;
            }

            gambar.src = URL.createObjectURL(berkas);
            gambar.classList.remove('d-none');
            if (placeholder) {
                placeholder.classList.add('d-none');
            }
        });
    })();
</script>
<?php $this->endSection() ?>
