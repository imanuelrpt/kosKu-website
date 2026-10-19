<?php

namespace App\Controllers;

use App\Models\SettingModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Profil - ubah data diri dan password.
 * Dipakai oleh admin maupun penghuni (layout menyesuaikan peran).
 */
class Profil extends BaseController
{
    protected UserModel $userModel;

    /**
     * Halaman profil yang dipakai peran saat ini.
     * Ditimpa oleh App\Controllers\Penghuni\Profil agar penghuni tetap
     * berada di dalam portal penghuni (bukan halaman profil admin).
     */
    protected string $halamanProfil = 'profil';

    /** View halaman profil; juga ditimpa oleh portal penghuni. */
    protected string $viewProfil = 'profil/index';

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $this->userModel = new UserModel();
    }

    public function index(): string
    {
        return $this->render($this->viewProfil, [
            'title'  => 'Profil Saya',
            'profil' => $this->userModel->find(user_id()),
            'layout' => layout_peran(),
        ]);
    }

    /**
     * Simpan perubahan nama / email / nomor HP.
     */
    public function update()
    {
        $id = user_id();

        if (! $this->validate([
            'name'  => ['label' => 'Nama Lengkap', 'rules' => 'required|min_length[3]|max_length[100]'],
            'email' => ['label' => 'Email', 'rules' => "required|valid_email|max_length[150]|is_unique[users.email,id,{$id}]"],
            'phone' => ['label' => 'Nomor HP', 'rules' => 'permit_empty|max_length[30]'],
        ])) {
            return redirect()->back()->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $this->userModel->update($id, [
            'name'  => trim((string) $this->request->getPost('name')),
            'email' => strtolower(trim((string) $this->request->getPost('email'))),
            'phone' => trim((string) $this->request->getPost('phone')),
        ]);

        if ($this->userModel->errors() !== []) {
            return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
        }

        // Perbarui data di session agar nama di header langsung berubah.
        session()->set('user', UserModel::untukSession($this->userModel->find($id)));

        return redirect()->to(base_url($this->halamanProfil))->with('sukses', 'Profil berhasil diperbarui.');
    }

    /**
     * Ganti password (wajib memasukkan password lama).
     */
    public function updatePassword()
    {
        $id = user_id();

        if (! $this->validate([
            'password_lama'    => ['label' => 'Password Lama', 'rules' => 'required'],
            'password'         => ['label' => 'Password Baru', 'rules' => 'required|min_length[6]|max_length[100]'],
            'password_confirm' => ['label' => 'Ulangi Password Baru', 'rules' => 'required|matches[password]'],
        ])) {
            return redirect()->back()->with('errors', $this->validator->getErrors());
        }

        $user = $this->userModel->find($id);

        if ($user === null || ! password_verify((string) $this->request->getPost('password_lama'), $user['password'])) {
            return redirect()->back()->with('gagal', 'Password lama tidak sesuai.');
        }

        $this->userModel->update($id, [
            'password' => (string) $this->request->getPost('password'),
        ]);

        return redirect()->to(base_url($this->halamanProfil))->with('sukses', 'Password berhasil diganti.');
    }
}
