<?php
/**
 * Daftar kamar: ringkasan status, pencarian, filter, tabel, pagination.
 */
$this->extend('layouts/admin');

$warnaRingkas = [
    'total'     => ['Total Kamar', 'primary', 'door-open'],
    'terisi'    => ['Terisi',      'success', 'person-check'],
    'kosong'    => ['Kosong',      'info',    'door-closed'],
    'perbaikan' => ['Perbaikan',   'warning', 'tools'],
];
$opsiStatus = ['kosong' => 'Kosong', 'terisi' => 'Terisi', 'perbaikan' => 'Perbaikan'];
?>

<?php $this->section('content') ?>

<div class="row g-3 mb-3">
    <?php foreach ($warnaRingkas as $kunci => [$label, $warna, $ikon]): ?>
        <div class="col-6 col-lg-3">
            <div class="card stat-card shadow-sm h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon text-bg-<?= $warna ?>"><i class="bi bi-<?= $ikon ?>"></i></span>
                    <div>
                        <div class="text-secondary small"><?= esc($label) ?></div>
                        <div class="fs-4 fw-bold lh-1"><?= (int) ($ringkasan[$kunci] ?? 0) ?></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center">
            <form class="row g-2 align-items-center" method="get" action="<?= base_url('kamar') ?>">
                <div class="col-auto">
                    <input type="search" name="cari" class="form-control form-control-sm"
                           placeholder="Cari nomor kamar / fasilitas..." value="<?= esc($cari) ?>">
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
                        <a href="<?= base_url('kamar') ?>" class="btn btn-sm btn-light">Reset</a>
                    <?php endif; ?>
                </div>
            </form>

            <a href="<?= base_url('kamar/tambah') ?>" class="btn btn-sm btn-success">
                <i class="bi bi-plus-lg me-1"></i>Tambah Kamar
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kamar</th>
                    <th class="text-center">Lantai</th>
                    <th class="text-end">Harga / Bulan</th>
                    <th>Fasilitas</th>
                    <th>Penghuni</th>
                    <th class="text-center">Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($kamar === []): ?>
                <tr>
                    <td colspan="7" class="text-center text-secondary py-5">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        <?= $cari !== '' || $status !== '' ? 'Tidak ada kamar yang cocok dengan pencarian.' : 'Belum ada data kamar.' ?>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach ($kamar as $k): ?>
                <?php $penghuni = $petaPenghuni[(int) $k['id']] ?? null; ?>
                <tr>
                    <td>
                        <div class="fw-semibold"><?= esc($k['room_number']) ?></div>
                        <?php if (! empty($k['notes'])): ?>
                            <div class="small text-secondary"><?= esc($k['notes']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= (int) $k['floor'] ?></td>
                    <td class="text-end text-nowrap"><?= rupiah($k['price']) ?></td>
                    <td class="small text-secondary" style="max-width:260px;"><?= esc($k['facilities'] ?? '-') ?></td>
                    <td>
                        <?php if ($penghuni !== null): ?>
                            <a href="<?= base_url('penghuni/' . $penghuni['tenant_id']) ?>" class="text-decoration-none fw-semibold">
                                <?= esc($penghuni['tenant_name']) ?>
                            </a>
                            <div class="small text-secondary">Sejak <?= esc(tanggal_indo($penghuni['start_date'])) ?></div>
                        <?php else: ?>
                            <span class="text-secondary">&mdash;</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= badge_kamar($k['status']) ?></td>
                    <td class="text-end text-nowrap">
                        <?php if ($k['status'] === 'kosong' && $penghuni === null): ?>
                            <a href="<?= base_url('sewa/checkin?kamar=' . $k['id']) ?>" class="btn btn-sm btn-outline-success"
                               title="Check-in penghuni ke kamar ini">
                                <i class="bi bi-box-arrow-in-right"></i>
                            </a>
                        <?php endif; ?>
                        <a href="<?= base_url('kamar/ubah/' . $k['id']) ?>" class="btn btn-sm btn-outline-primary"
                           title="Ubah kamar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger" title="Hapus kamar"
                                data-bs-toggle="modal" data-bs-target="#modalHapus"
                                data-hapus-url="<?= base_url('kamar/hapus/' . $k['id']) ?>"
                                data-hapus-nama="Kamar <?= esc($k['room_number']) ?>"
                                data-hapus-catatan="Riwayat sewa kamar ini tetap tersimpan di sistem.">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card-footer bg-white d-flex flex-wrap gap-2 justify-content-between align-items-center">
        <div class="small text-secondary"><?= info_halaman($pager, 'kamar') ?></div>
        <?= $pager->links('default', 'kosku') ?>
    </div>
</div>

<?= view('partials/modal_hapus') ?>
<?php $this->endSection() ?>

<?php $this->section('scripts') ?>
<?= view('partials/script_hapus') ?>
<?php $this->endSection() ?>
