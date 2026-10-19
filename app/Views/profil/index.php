<?php
/**
 * Profil saya - ubah data diri & password.
 * Layout menyesuaikan peran ($layout dikirim dari controller).
 */
$this->extend($layout ?? 'layouts/admin');
?>

<?php $this->section('content') ?>
<div class="row g-4">
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person-vcard me-2"></i>Data Diri</div>
            <div class="card-body">
                <?= form_open('profil') ?>
                    <div class="mb-3">
                        <label class="form-label required" for="name">Nama Lengkap</label>
                        <input type="text" name="name" id="name" class="form-control"
                               value="<?= esc(old('name', $profil['name'] ?? '')) ?>" required>
                        <?php if (session('errors.name')): ?>
                            <div class="text-danger small mt-1"><?= esc(session('errors.name')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required" for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control"
                               value="<?= esc(old('email', $profil['email'] ?? '')) ?>" required>
                        <?php if (session('errors.email')): ?>
                            <div class="text-danger small mt-1"><?= esc(session('errors.email')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="phone">Nomor HP</label>
                        <input type="text" name="phone" id="phone" class="form-control"
                               value="<?= esc(old('phone', $profil['phone'] ?? '')) ?>" placeholder="08xx-xxxx-xxxx">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Peran</label>
                        <input type="text" class="form-control" value="<?= esc(ucfirst($profil['role'] ?? '')) ?>" disabled>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                <?= form_close() ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-shield-lock me-2"></i>Ganti Password</div>
            <div class="card-body">
                <?= form_open('profil/password') ?>
                    <div class="mb-3">
                        <label class="form-label required" for="password_lama">Password Lama</label>
                        <input type="password" name="password_lama" id="password_lama" class="form-control" required>
                        <?php if (session('errors.password_lama')): ?>
                            <div class="text-danger small mt-1"><?= esc(session('errors.password_lama')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required" for="password">Password Baru</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required>
                        <?php if (session('errors.password')): ?>
                            <div class="text-danger small mt-1"><?= esc(session('errors.password')) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-3">
                        <label class="form-label required" for="password_confirm">Ulangi Password Baru</label>
                        <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
                        <?php if (session('errors.password_confirm')): ?>
                            <div class="text-danger small mt-1"><?= esc(session('errors.password_confirm')) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-key me-1"></i>Ganti Password
                    </button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>
<?php $this->endSection() ?>
