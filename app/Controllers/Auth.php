<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Auth - login, pendaftaran mandiri penghuni, dan logout.
 */
class Auth extends BaseController
{
    private UserModel $userModel;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->userModel = new UserModel();
    }

    /**
     * Form login (GET).
     */
    public function login(): string
    {
        return $this->render('auth/login', [
            'title'               => 'Masuk',
            'pendaftaranDibuka'   => setting_aktif('registration_enabled'),
        ]);
    }

    /**
     * Proses login (POST).
     */
    public function attemptLogin()
    {
        if (! $this->validate([
            'email'    => ['label' => 'Email', 'rules' => 'required|valid_email'],
            'password' => ['label' => 'Password', 'rules' => 'required'],
        ])) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email    = strtolower(trim((string) $this->request->getPost('email')));
        $password = (string) $this->request->getPost('password');

        // Batasi percobaan login: 5 kali per menit per kombinasi IP + email.
        $throttler = service('throttler');
        $kunci     = md5($this->request->getIPAddress() . '|' . $email);

        if (! $throttler->check($kunci, 5, MINUTE)) {
            return redirect()->back()->withInput()->with('gagal',
                'Terlalu banyak percobaan masuk. Silakan coba lagi dalam 1 menit.');
        }

        $user = $this->userModel->cariEmail($email);

        // Pesan sengaja disamakan agar tidak membocorkan email mana yang terdaftar.
        if ($user === null || ! password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()
                ->with('gagal', 'Email atau password salah.');
        }

        // Login berhasil - reset penghitung percobaan.
        $throttler->remove($kunci);

        // Cegah session fixation.
        session()->regenerate();
        session()->set('user', UserModel::untukSession($user));

        $sapaan = 'Selamat datang, ' . $user['name'] . '!';

        // Kembalikan ke halaman yang tadi ingin dibuka (bila sesuai peran).
        $tujuan = (string) session()->get('redirect_url');
        session()->remove('redirect_url');

        if ($tujuan !== '' && $this->tujuanSesuaiPeran($tujuan, $user['role'])) {
            return redirect()->to(base_url($tujuan))->with('sukses', $sapaan);
        }

        return redirect()->to(beranda_peran($user['role']))->with('sukses', $sapaan);
    }

    /**
     * Form pendaftaran mandiri (GET) - hanya bila diaktifkan admin.
     */
    public function register()
    {
        if (! setting_aktif('registration_enabled')) {
            return redirect()->to(base_url('login'))->with('info',
                'Pendaftaran mandiri sedang ditutup. Silakan hubungi pemilik kos untuk dibuatkan akun.');
        }

        return $this->render('auth/register', [
            'title' => 'Daftar Akun Penghuni',
        ]);
    }

    /**
     * Proses pendaftaran mandiri (POST).
     * Akun baru selalu berperan "penghuni".
     */
    public function attemptRegister()
    {
        if (! setting_aktif('registration_enabled')) {
            return redirect()->to(base_url('login'))
                ->with('gagal', 'Pendaftaran mandiri sedang ditutup.');
        }

        if (! $this->validate([
            'name'             => ['label' => 'Nama Lengkap', 'rules' => 'required|min_length[3]|max_length[100]'],
            'email'            => ['label' => 'Email', 'rules' => 'required|valid_email|max_length[150]|is_unique[users.email]'],
            'phone'            => ['label' => 'Nomor HP', 'rules' => 'permit_empty|max_length[30]'],
            'password'         => ['label' => 'Password', 'rules' => 'required|min_length[6]|max_length[100]'],
            'password_confirm' => ['label' => 'Ulangi Password', 'rules' => 'required|matches[password]'],
        ])) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->userModel->insert([
            'name'     => trim((string) $this->request->getPost('name')),
            'email'    => strtolower(trim((string) $this->request->getPost('email'))),
            'phone'    => trim((string) $this->request->getPost('phone')),
            'password' => (string) $this->request->getPost('password'),
            'role'     => 'penghuni', // tidak bisa didaftarkan sebagai admin
        ]);

        if ($this->userModel->errors() !== []) {
            return redirect()->back()->withInput()
                ->with('errors', $this->userModel->errors());
        }

        return redirect()->to(base_url('login'))
            ->with('sukses', 'Akun berhasil dibuat. Silakan masuk menggunakan email dan password Anda.');
    }

    /**
     * Logout.
     */
    public function logout()
    {
        session()->remove('user');
        session()->remove('redirect_url');
        session()->regenerate();

        return redirect()->to(base_url('login'))->with('sukses', 'Anda telah keluar dari aplikasi.');
    }

    /**
     * Cek apakah URL tujuan masih dalam area perannya (mencegah redirect lintas peran).
     */
    private function tujuanSesuaiPeran(string $tujuan, string $role): bool
    {
        $tujuan  = ltrim($tujuan, '/');
        $areaPgh = str_starts_with($tujuan, 'penghuni');

        return $role === 'penghuni' ? $areaPgh : ! $areaPgh;
    }
}
