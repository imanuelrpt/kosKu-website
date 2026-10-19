<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * AuthFilter - memastikan pengguna sudah login.
 * Kalau belum, diarahkan ke halaman login dan URL tujuan disimpan dulu.
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth');

        if (user_login() !== null) {
            return null; // lanjutkan request
        }

        // Simpan tujuan agar bisa dibuka lagi setelah berhasil login.
        $tujuan = (string) uri_string();
        if ($tujuan !== '' && $tujuan !== 'login') {
            session()->set('redirect_url', $tujuan);
        }

        return redirect()->to(base_url('login'))
            ->with('info', 'Silakan masuk terlebih dahulu untuk melanjutkan.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // tidak ada aksi
    }
}
