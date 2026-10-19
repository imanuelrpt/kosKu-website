<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * RoleFilter - membatasi halaman berdasarkan peran.
 *
 * Pemakaian di route:  ['filter' => 'role:admin']
 *                      ['filter' => 'role:admin,penghuni']
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth');

        $user = user_login();

        // Belum login -> serahkan ke AuthFilter (atau arahkan ke login).
        if ($user === null) {
            return redirect()->to(base_url('login'))
                ->with('info', 'Silakan masuk terlebih dahulu.');
        }

        $peranDiizinkan = $arguments ?? [];

        if ($peranDiizinkan !== [] && ! in_array($user['role'], $peranDiizinkan, true)) {
            return redirect()->to(beranda_peran($user['role']))
                ->with('gagal', 'Anda tidak berhak mengakses halaman tersebut.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // tidak ada aksi
    }
}
