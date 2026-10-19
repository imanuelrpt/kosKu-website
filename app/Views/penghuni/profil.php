<?php
/**
 * Profil Saya (portal penghuni) - data akun, data penghuni, dan ganti password.
 *
 * Variabel: $profil (akun login), $tenant (data penghuni, boleh null), $sewa
 */
$this->extend('layouts/tenant');
?>

<?php $this->section('content') ?>

<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div>
        <h2 class="h5 mb-0">Profil Saya</h2>
        <div class="small text-secondary">Kelola data akun dan password Anda.</div>
    </div>
    <a href="<?= base_url('penghuni/beranda') ?>" class="btn btn-sm btn-light">
        <i class="bi bi-arrow-left me-1"></i>Kembali ke Dashboard
    </a>
</div>

<div class="row g-3">
    <!-- Kolom kiri: identitas -->
    <div class="col-12 col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-body text-center py-4">
                <span class="avatar-initial mx-auto mb-3" style="width:84px;height:84px;font-size:1.6rem;">
                    <?= esc(inisial_nama($profil['name'] ?? 'P')) ?>
                </span>
                <h3 class="h5 mb-1"><?= esc($profil['name'] ?? '-') ?></h3>
                <div class="small text-secondary mb-2"><?= esc($profil['email'] ?? '-') ?></div>
                <span class="badge text-bg-primary"><?= esc(ucfirst($profil['role'] ?? 'penghuni')) ?></span>
                <?php if ($sewa !== null): ?>
                    <span class="badge text-bg-light border">Kamar <?= esc($sewa['room_number']) ?></span>
                <?php endif; ?>
                <?php if (! empty($profil['created_at'])): ?>
                    <div class="small text-secondary mt-3">
                        Akun dibuat <?= esc(tanggal_indo($profil['created_at'])) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-vcard me-2"></i>Data Penghuni
            </div>
            <?php if ($tenant === null): ?>
                <div class="card-body">
                    <p class="text-secondary small mb-0">
                        Akun Anda belum ditautkan ke data penghuni oleh pengelola kos.
                        Setelah ditautkan, data KTP, alamat, dan kontak darurat akan tampil di sini.
                    </p>
                </div>
            <?php else: ?>
                <ul class="list-group list-group-flush small">
                    <li class="list-group-item">
                        <div class="text-secondary">Nama Sesuai Data Kos</div>
                        <div class="fw-semibold"><?= esc($tenant['name']) ?></div>
                    </li>
                    <li class="list-group-item">
                        <div class="text-secondary">No. KTP</div>
                        <div><?= esc($tenant['id_number'] !== null && $tenant['id_number'] !== '' ? $tenant['id_number'] : '-') ?></div>
                    </li>
                    <li class="list-group-item">
                        <div class="text-secondary">Nomor HP Terdaftar</div>
                        <div><?= esc($tenant['phone'] ?? '-') ?></div>
                    </li>
                    <li class="list-group-item">
                        <div class="text-secondary">Alamat Asal</div>
                        <div><?= esc($tenant['address'] ?? '-') ?></div>
                    </li>
                    <li class="list-group-item">
                        <div class="text-secondary">Kontak Darurat</div>
                        <div><?= esc($tenant['emergency_contact'] ?? '-') ?></div>
                    </li>
                </ul>
                <div class="card-body py-2">
                    <div class="small text-secondary">
                        <i class="bi bi-info-circle me-1"></i>
                        Data di atas dikelola oleh pengelola kos. Hubungi pengelola bila ada yang perlu diubah.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Kolom kanan: form -->
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-person-gear me-2"></i>Data Akun</div>
            <div class="card-body">
                <?= form_open('penghuni/profil') ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="name">Nama Lengkap</label>
                            <input type="text" name="name" id="name" class="form-control"
                                   value="<?= esc(old('name', $profil['name'] ?? '')) ?>" required>
                            <?php if (session('errors.name')): ?>
                                <div class="text-danger small mt-1"><?= esc(session('errors.name')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label required" for="email">Email</label>
                            <input type="email" name="email" id="email" class="form-control"
                                   value="<?= esc(old('email', $profil['email'] ?? '')) ?>" required>
                            <?php if (session('errors.email')): ?>
                                <div class="text-danger small mt-1"><?= esc(session('errors.email')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label" for="phone">Nomor HP (akun)</label>
                            <input type="text" name="phone" id="phone" class="form-control"
                                   value="<?= esc(old('phone', $profil['phone'] ?? '')) ?>" placeholder="08xx-xxxx-xxxx">
                            <div class="form-text">Dipakai untuk keperluan login dan pemberitahuan.</div>
                            <?php if (session('errors.phone')): ?>
                                <div class="text-danger small mt-1"><?= esc(session('errors.phone')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Peran</label>
                            <input type="text" class="form-control" value="<?= esc(ucfirst($profil['role'] ?? '')) ?>" disabled>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                <?= form_close() ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-shield-lock me-2"></i>Ganti Password</div>
            <div class="card-body">
                <?= form_open('penghuni/profil/password') ?>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label required" for="password_lama">Password Lama</label>
                            <input type="password" name="password_lama" id="password_lama" class="form-control" required>
                            <?php if (session('errors.password_lama')): ?>
                                <div class="text-danger small mt-1"><?= esc(session('errors.password_lama')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label required" for="password">Password Baru</label>
                            <input type="password" name="password" id="password" class="form-control"
                                   placeholder="Minimal 6 karakter" required>
                            <?php if (session('errors.password')): ?>
                                <div class="text-danger small mt-1"><?= esc(session('errors.password')) ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label required" for="password_confirm">Ulangi Password Baru</label>
                            <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
                            <?php if (session('errors.password_confirm')): ?>
                                <div class="text-danger small mt-1"><?= esc(session('errors.password_confirm')) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning mt-3">
                        <i class="bi bi-key me-1"></i>Ganti Password
                    </button>
                <?= form_close() ?>
            </div>
        </div>
    </div>
</div>

<?php $this->endSection() ?>
