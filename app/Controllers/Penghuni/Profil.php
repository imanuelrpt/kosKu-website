<?php

namespace App\Controllers\Penghuni;

use App\Controllers\Profil as ProfilDasar;
use App\Models\OccupancyModel;

/**
 * Profil penghuni di dalam portal penghuni.
 *
 * Logika ubah data diri & ganti password diwarisi dari App\Controllers\Profil
 * (dipakai juga oleh admin); yang berbeda hanya halaman dan tujuan redirect
 * agar penghuni tetap berada di portal penghuni.
 */
class Profil extends ProfilDasar
{
    protected string $halamanProfil = 'penghuni/profil';
    protected string $viewProfil    = 'penghuni/profil';

    public function index(): string
    {
        $tenant = penghuni_login();

        return $this->render($this->viewProfil, [
            'title'  => 'Profil Saya',
            'profil' => $this->userModel->find(user_id()),
            'tenant' => $tenant,
            'sewa'   => $tenant !== null ? (new OccupancyModel())->sewaAktifTenant((int) $tenant['id']) : null,
        ]);
    }
}
