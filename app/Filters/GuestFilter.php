<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * GuestFilter - halaman yang hanya untuk pengunjung (belum login).
 * Pengguna yang sudah login langsung diarahkan ke beranda perannya.
 */
class GuestFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth');

        $user = user_login();

        if ($user === null) {
            return null; // lanjutkan request
        }

        return redirect()->to(beranda_peran($user['role']));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // tidak ada aksi
    }
}
