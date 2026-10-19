<?php
/**
 * Halaman login.
 */
$this->extend('layouts/auth');
?>

<?php $this->section('content') ?>
<h2 class="h5 mb-1">Masuk ke KosKu</h2>
<p class="text-secondary small mb-4">Gunakan akun yang diberikan pemilik kos.</p>

<?= form_open('login', ['autocomplete' => 'off']) ?>
    <div class="mb-3">
        <label class="form-label required" for="email">Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" id="email" class="form-control <?= session('errors.email') ? 'is-invalid' : '' ?>"
                   value="<?= esc(old('email')) ?>" placeholder="nama@email.com" required autofocus>
        </div>
        <?php if (session('errors.email')): ?>
            <div class="text-danger small mt-1"><?= esc(session('errors.email')) ?></div>
        <?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label required" for="password">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control <?= session('errors.password') ? 'is-invalid' : '' ?>"
                   placeholder="Password" required>
            <button class="btn btn-outline-secondary" type="button" id="tglPassword" tabindex="-1" aria-label="Tampilkan password">
                <i class="bi bi-eye"></i>
            </button>
        </div>
        <?php if (session('errors.password')): ?>
            <div class="text-danger small mt-1"><?= esc(session('errors.password')) ?></div>
        <?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary w-100 py-2">
        <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
    </button>
<?= form_close() ?>

<?php if ($pendaftaranDibuka): ?>
    <hr class="my-4">
    <p class="text-center small text-secondary mb-0">
        Belum punya akun?
        <a href="<?= base_url('register') ?>" class="text-decoration-none">Daftar sebagai penghuni</a>
    </p>
<?php else: ?>
    <hr class="my-4">
    <p class="text-center small text-secondary mb-0">
        Belum punya akun? Hubungi pemilik kos untuk dibuatkan akun.
    </p>
<?php endif; ?>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<script>
    // Tombol lihat/sembunyikan password.
    document.getElementById('tglPassword')?.addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon  = this.querySelector('i');
        const lihat = input.type === 'password';
        input.type  = lihat ? 'text' : 'password';
        icon.className = lihat ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
</script>
<?php $this->endSection() ?>
