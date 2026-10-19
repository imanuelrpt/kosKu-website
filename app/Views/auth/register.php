<?php
/**
 * Halaman pendaftaran mandiri penghuni.
 */
$this->extend('layouts/auth');
?>

<?php $this->section('content') ?>
<h2 class="h5 mb-1">Daftar Akun Penghuni</h2>
<p class="text-secondary small mb-4">
    Setelah mendaftar, akun Anda akan ditautkan ke data penghuni oleh pemilik kos.
</p>

<?= form_open('register', ['autocomplete' => 'off']) ?>
    <div class="mb-3">
        <label class="form-label required" for="name">Nama Lengkap</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="name" id="name" class="form-control"
                   value="<?= esc(old('name')) ?>" placeholder="Nama sesuai KTP" required autofocus>
        </div>
        <?php if (session('errors.name')): ?>
            <div class="text-danger small mt-1"><?= esc(session('errors.name')) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label required" for="email">Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" id="email" class="form-control"
                   value="<?= esc(old('email')) ?>" placeholder="nama@email.com" required>
        </div>
        <?php if (session('errors.email')): ?>
            <div class="text-danger small mt-1"><?= esc(session('errors.email')) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label" for="phone">Nomor HP</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
            <input type="text" name="phone" id="phone" class="form-control"
                   value="<?= esc(old('phone')) ?>" placeholder="08xx-xxxx-xxxx">
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label required" for="password">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control" placeholder="Minimal 6 karakter" required>
        </div>
        <?php if (session('errors.password')): ?>
            <div class="text-danger small mt-1"><?= esc(session('errors.password')) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label required" for="password_confirm">Ulangi Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
            <input type="password" name="password_confirm" id="password_confirm" class="form-control" placeholder="Ulangi password" required>
        </div>
        <?php if (session('errors.password_confirm')): ?>
            <div class="text-danger small mt-1"><?= esc(session('errors.password_confirm')) ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2">
        <i class="bi bi-person-plus me-1"></i>Daftar
    </button>
<?= form_close() ?>

<hr class="my-4">
<p class="text-center small text-secondary mb-0">
    Sudah punya akun? <a href="<?= base_url('login') ?>" class="text-decoration-none">Masuk di sini</a>
</p>
<?php $this->endSection() ?>
