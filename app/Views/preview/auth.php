<?php
/**
 * Preview layout login (Tahap 1).
 */
$this->extend('layouts/auth');
?>

<?php $this->section('content') ?>
<h2 class="h5 mb-1">Masuk ke KosKu</h2>
<p class="text-secondary small mb-4">Contoh tampilan formulir login.</p>

<form>
    <div class="mb-3">
        <label class="form-label required" for="email">Email</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control" id="email" placeholder="nama@email.com" required>
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label required" for="password">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control" id="password" placeholder="Password" required>
        </div>
    </div>
    <button type="submit" class="btn btn-primary w-100 py-2">
        <i class="bi bi-box-arrow-in-right me-1"></i>Masuk
    </button>
</form>

<div class="text-center mt-3">
    <a href="<?= base_url() ?>" class="small text-decoration-none">&larr; Kembali ke halaman cek sistem</a>
</div>
<?php $this->endSection() ?>
