<?php

namespace App\Controllers;

use App\Models\PaymentModel;
use App\Models\TenantModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Berkas - menyajikan berkas unggahan (foto penghuni, bukti transfer).
 *
 * Berkas aslinya ada di luar folder publik, jadi harus lewat controller ini.
 * Hak akses diperiksa: admin boleh semua, penghuni hanya berkas miliknya.
 */
class Berkas extends BaseController
{
    /** Folder unggahan yang boleh diakses lewat controller ini. */
    private const FOLDER_DIIZINKAN = ['penghuni', 'pembayaran'];

    public function kirim(string $folder, string $nama): ResponseInterface
    {
        $folder = strtolower($folder);

        if (! in_array($folder, self::FOLDER_DIIZINKAN, true)) {
            throw PageNotFoundException::forPageNotFound('Folder berkas tidak dikenali.');
        }

        $path = path_berkas($folder, $nama);

        if ($path === null || ! is_file($path)) {
            throw PageNotFoundException::forPageNotFound('Berkas tidak ditemukan.');
        }

        // Penghuni hanya boleh membuka berkas miliknya sendiri.
        if (is_penghuni() && ! $this->milikPenghuniYangLogin($folder, $path)) {
            throw PageNotFoundException::forPageNotFound('Berkas tidak ditemukan.');
        }

        // Tentukan MIME asli dari isi berkas.
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($path) ?: 'application/octet-stream';

        return $this->response
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'inline; filename="' . basename($path) . '"')
            ->setHeader('X-Content-Type-Options', 'nosniff')
            ->setHeader('Cache-Control', 'private, max-age=86400')
            ->setBody((string) file_get_contents($path));
    }

    /**
     * Cek kepemilikan berkas untuk pengguna berperan penghuni.
     */
    private function milikPenghuniYangLogin(string $folder, string $path): bool
    {
        $nama = basename($path);
        $uid  = user_id();

        if ($uid === null) {
            return false;
        }

        if ($folder === 'penghuni') {
            $tenant = (new TenantModel())
                ->where('user_id', $uid)
                ->where('photo', $nama)
                ->first();

            return $tenant !== null;
        }

        // Bukti transfer: cocokkan pemilik lewat invoices -> occupancies -> tenants.
        $bayar = (new PaymentModel())->cariLewatBukti($nama);

        return $bayar !== null && (int) ($bayar['pemilik_user_id'] ?? 0) === $uid;
    }
}
