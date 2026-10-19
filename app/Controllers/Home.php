<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use Throwable;

/**
 * Home - pintu masuk aplikasi.
 * "/" mengarahkan ke login atau beranda sesuai status login.
 */
class Home extends BaseController
{
    public function index()
    {
        helper('auth');

        if (user_login() !== null) {
            return redirect()->to(beranda_peran());
        }

        return redirect()->to(base_url('login'));
    }

    /**
     * Halaman "Cek Sistem" - memastikan environment siap pakai.
     * Hanya tersedia pada environment development.
     */
    public function cekSistem(): string
    {
        if (ENVIRONMENT !== 'development') {
            throw PageNotFoundException::forPageNotFound();
        }

        // Daftar ekstensi PHP yang dibutuhkan aplikasi.
        $ekstensiWajib = ['intl', 'mbstring', 'curl', 'mysqli', 'gd', 'zip'];
        $ekstensi      = [];
        foreach ($ekstensiWajib as $ext) {
            $ekstensi[$ext] = extension_loaded($ext);
        }

        // Uji koneksi database + ringkasan data (untuk verifikasi seeder).
        $db      = ['ok' => false, 'pesan' => '', 'versi' => '', 'nama' => ''];
        $ringkas = [];
        try {
            $conn = db_connect();
            $row  = $conn->query('SELECT VERSION() AS versi, DATABASE() AS nama')->getRow();
            $db   = [
                'ok'    => true,
                'pesan' => 'Koneksi database berhasil.',
                'versi' => $row->versi ?? '-',
                'nama'  => $row->nama ?? '-',
            ];

            // Jumlah baris tiap tabel (null jika tabel belum ada).
            foreach (['users', 'rooms', 'tenants', 'occupancies', 'invoices', 'payments', 'expenses'] as $tabel) {
                try {
                    $ringkas[$tabel] = $conn->table($tabel)->countAllResults();
                } catch (Throwable $e) {
                    $ringkas[$tabel] = null;
                }
            }
        } catch (Throwable $e) {
            $db['pesan'] = $e->getMessage();
        }

        return $this->render('home', [
            'title'       => 'Cek Sistem',
            'phpVersion'  => PHP_VERSION,
            'ciVersion'   => \CodeIgniter\CodeIgniter::CI_VERSION,
            'ekstensi'    => $ekstensi,
            'db'          => $db,
            'ringkas'     => $ringkas,
            'environment' => ENVIRONMENT,
            'timezone'    => date_default_timezone_get(),
        ]);
    }

    /**
     * Halaman preview layout (hanya untuk development).
     */
    public function preview(string $jenis): string
    {
        if (ENVIRONMENT !== 'development') {
            throw PageNotFoundException::forPageNotFound();
        }

        if (! in_array($jenis, ['admin', 'tenant', 'auth'], true)) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->render('preview/' . $jenis, [
            'title' => 'Preview Layout ' . ucfirst($jenis),
        ]);
    }
}
