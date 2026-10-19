<?php
/**
 * Daftar penghuni: pencarian, tabel, pagination.
 */
$this->extend('layouts/admin');
?>

<?php $this->section('content') ?>

<div class="row g-3 mb-3">
    <div class="col-12 col-md-6">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-primary"><i class="bi bi-people"></i></span>
                <div>
                    <div class="text-secondary small">Total Penghuni</div>
                    <div class="fs-4 fw-bold lh-1"><?= (int) $ringkasan['total'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-success"><i class="bi bi-key"></i></span>
                <div>
                    <div class="text-secondary small">Sedang Menyewa</div>
                    <div class="fs-4 fw-bold lh-1"><?= (int) $ringkasan['menyewa'] ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <form class="row g-2 align-items-center" method="get" action="<?= base_url('penghuni') ?>">
                <div class="col-auto">
                    <input type="search" name="cari" class="form-control form-control-sm"
                           placeholder="Cari nama / nomor HP / KTP..." value="<?= esc($cari) ?>" style="min-width:240px;">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-search me-1"></i>Cari
                    </button>
                    <?php if ($cari !== ''): ?>
                        <a href="<?= base_url('penghuni') ?>" class="btn btn-sm btn-light">Reset</a>
                    <?php endif; ?>
                </div>
            </form>

            <a href="<?= base_url('penghuni/tambah') ?>" class="btn btn-sm btn-success">
                <i class="bi bi-person-plus me-1"></i>Tambah Penghuni
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Penghuni</th>
                    <th>Kontak</th>
                    <th>Nomor KTP</th>
                    <th>Kamar</th>
                    <th class="text-center">Akun</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($penghuni === []): ?>
                <tr>
                    <td colspan="6" class="text-center text-secondary py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        <?= $cari !== '' ? 'Tidak ada penghuni yang cocok dengan pencarian.' : 'Belum ada data penghuni.' ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($penghuni as $p): ?>
                <?php $kamar = $petaKamar[(int) $p['id']] ?? null; ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?php if (! empty($p['photo'])): ?>
                                <img src="<?= esc(url_berkas('penghuni', $p['photo']), 'attr') ?>" alt="Foto <?= esc($p['name'], 'attr') ?>"
                                     class="avatar-initial object-fit-cover" style="object-fit:cover;">
                            <?php else: ?>
                                <span class="avatar-initial"><?= esc(inisial_nama($p['name'])) ?></span>
                            <?php endif; ?>
                            <div>
                                <a href="<?= base_url('penghuni/' . $p['id']) ?>" class="text-decoration-none fw-semibold">
                                    <?= esc($p['name']) ?>
                                </a>
                                <div class="small text-secondary">
                                    <?= esc($p['phone'] ?? 'Tanpa nomor HP') ?>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="small text-secondary" style="max-width:220px;">
                        <?= esc($p['address'] ?? '-') ?>
                        <?php if (! empty($p['emergency_contact'])): ?>
                            <div class="mt-1"><i class="bi bi-telephone-forward me-1"></i><?= esc($p['emergency_contact']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-nowrap"><?= esc($p['id_number'] ?? '-') ?></td>
                    <td>
                        <?php if ($kamar !== null): ?>
                            <span class="fw-semibold"><?= esc($kamar['room_number']) ?></span>
                            <div class="small text-secondary">Lantai <?= (int) $kamar['floor'] ?></div>
                        <?php else: ?>
                            <span class="badge text-bg-secondary">Tidak menyewa</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($p['user_id'] === null): ?>
                            <span class="badge text-bg-light text-secondary border">Belum tertaut</span>
                        <?php else: ?>
                            <span class="badge text-bg-primary"><i class="bi bi-check-lg me-1"></i>Tertaut</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-nowrap">
                        <a href="<?= base_url('penghuni/' . $p['id']) ?>" class="btn btn-sm btn-outline-secondary" title="Detail">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="<?= base_url('penghuni/ubah/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary" title="Ubah">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus"
                                data-bs-toggle="modal" data-bs-target="#modalHapus"
                                data-hapus-url="<?= base_url('penghuni/hapus/' . $p['id']) ?>"
                                data-hapus-nama="penghuni <?= esc($p['name']) ?>"
                                data-hapus-catatan="Riwayat sewa dan pembayaran penghuni ini tetap tersimpan di sistem.">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div class="small text-secondary"><?= info_halaman($pager, 'penghuni') ?></div>
        <?= $pager->links('default', 'kosku') ?>
    </div>
</div>

<?= view('partials/modal_hapus') ?>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<?= view('partials/script_hapus') ?>
<?php $this->endSection() ?>
