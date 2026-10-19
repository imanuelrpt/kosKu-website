<?php
/**
 * Daftar sewa: ringkasan, pencarian, filter status, tabel, pagination.
 */
$this->extend('layouts/admin');

$opsiStatus = ['aktif' => 'Sedang menyewa', 'selesai' => 'Sudah check-out'];
?>

<?php $this->section('content') ?>

<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-success"><i class="bi bi-key"></i></span>
                <div>
                    <div class="text-secondary small">Sewa Aktif</div>
                    <div class="fs-4 fw-bold lh-1"><?= (int) $ringkasan['aktif'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-info"><i class="bi bi-door-closed"></i></span>
                <div>
                    <div class="text-secondary small">Kamar Kosong</div>
                    <div class="fs-4 fw-bold lh-1"><?= (int) $ringkasan['kamar_kosong'] ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-primary"><i class="bi bi-cash-stack"></i></span>
                <div>
                    <div class="text-secondary small">Sewa Berjalan / Bulan</div>
                    <div class="fs-6 fw-bold lh-1"><?= rupiah($ringkasan['sewa_bulanan']) ?></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card stat-card shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <span class="stat-icon text-bg-secondary"><i class="bi bi-clock-history"></i></span>
                <div>
                    <div class="text-secondary small">Riwayat Selesai</div>
                    <div class="fs-4 fw-bold lh-1"><?= (int) $ringkasan['selesai'] ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <form class="row g-2 align-items-center" method="get" action="<?= base_url('sewa') ?>">
                <div class="col-auto">
                    <input type="search" name="cari" class="form-control form-control-sm"
                           placeholder="Cari nama / kamar / HP..." value="<?= esc($cari) ?>">
                </div>
                <div class="col-auto">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua status</option>
                        <?php foreach ($opsiStatus as $nilai => $label): ?>
                            <option value="<?= esc($nilai, 'attr') ?>" <?= $status === $nilai ? 'selected' : '' ?>>
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-search me-1"></i>Cari
                    </button>
                    <?php if ($cari !== '' || $status !== ''): ?>
                        <a href="<?= base_url('sewa') ?>" class="btn btn-sm btn-light">Reset</a>
                    <?php endif; ?>
                </div>
            </form>

            <a href="<?= base_url('sewa/checkin') ?>" class="btn btn-sm btn-success">
                <i class="bi bi-box-arrow-in-right me-1"></i>Check-in Baru
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Penghuni</th>
                    <th>Kamar</th>
                    <th>Mulai</th>
                    <th>Selesai</th>
                    <th class="text-end">Harga / Bulan</th>
                    <th class="text-end">Deposit</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($sewa === []): ?>
                <tr>
                    <td colspan="8" class="text-center text-secondary py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        <?= $cari !== '' || $status !== ''
                            ? 'Tidak ada data sewa yang cocok dengan pencarian.'
                            : 'Belum ada data sewa. Mulai dengan tombol Check-in Baru.' ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($sewa as $s): ?>
                <tr>
                    <td>
                        <a href="<?= base_url('penghuni/' . $s['tenant_id']) ?>" class="text-decoration-none fw-semibold">
                            <?= esc($s['tenant_name'] ?? '(penghuni dihapus)') ?>
                        </a>
                        <?php if (! empty($s['tenant_deleted_at'])): ?>
                            <span class="badge text-bg-light text-secondary border ms-1">dihapus</span>
                        <?php endif; ?>
                        <div class="small text-secondary"><?= esc($s['tenant_phone'] ?? 'Tanpa nomor HP') ?></div>
                    </td>
                    <td>
                        <div class="fw-semibold"><?= esc($s['room_number'] ?? '-') ?></div>
                        <div class="small text-secondary">Lantai <?= (int) ($s['floor'] ?? 0) ?></div>
                    </td>
                    <td class="text-nowrap"><?= esc(tanggal_indo($s['start_date'])) ?></td>
                    <td class="text-nowrap">
                        <?php if ($s['end_date'] !== null): ?>
                            <?= esc(tanggal_indo($s['end_date'])) ?>
                        <?php else: ?>
                            <span class="text-secondary">masih berjalan</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end text-nowrap"><?= rupiah($s['monthly_price']) ?></td>
                    <td class="text-end text-nowrap"><?= rupiah($s['deposit']) ?></td>
                    <td class="text-center"><?= badge_sewa($s['status']) ?></td>
                    <td class="text-end text-nowrap">
                        <?php if ($s['status'] === 'aktif'): ?>
                            <a href="<?= base_url('sewa/checkout/' . $s['id']) ?>" class="btn btn-sm btn-outline-warning"
                               title="Check-out penghuni ini">
                                <i class="bi bi-box-arrow-right"></i>
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger" title="Batalkan check-in"
                                    data-bs-toggle="modal" data-bs-target="#modalHapus"
                                    data-hapus-url="<?= base_url('sewa/batal/' . $s['id']) ?>"
                                    data-hapus-nama="check-in <?= esc($s['tenant_name'], 'attr') ?> di kamar <?= esc($s['room_number'], 'attr') ?>"
                                    data-hapus-catatan="Baris sewa ini akan dihapus dan kamar kembali tersedia. Tindakan ini tidak dapat dibatalkan.">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        <?php else: ?>
                            <a href="<?= base_url('penghuni/' . $s['tenant_id']) ?>" class="btn btn-sm btn-outline-secondary"
                               title="Lihat data penghuni">
                                <i class="bi bi-person"></i>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div class="small text-secondary"><?= info_halaman($pager, 'data sewa') ?></div>
        <?= $pager->links('default', 'kosku') ?>
    </div>
</div>

<?= view('partials/modal_hapus') ?>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<?= view('partials/script_hapus') ?>
<?php $this->endSection() ?>
