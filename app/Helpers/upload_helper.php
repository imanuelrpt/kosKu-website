<?php

/**
 * Helper unggah berkas.
 *
 * Semua unggahan disimpan di writable/uploads/<folder> - di luar folder
 * publik (public/) sehingga tidak bisa diakses langsung lewat URL.
 * Nama berkas selalu diacak. Untuk menampilkannya dipakai controller
 * Berkas yang memeriksa hak akses lebih dulu.
 *
 * Catatan: file helper di-include pada scope global, jadi nama kelas harus
 * ditulis lengkap (fully qualified).
 */

if (! function_exists('folder_unggahan')) {
    /**
     * Path absolut folder unggahan (dibuat bila belum ada).
     */
    function folder_unggahan(string $folder): string
    {
        $tujuan = rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $folder;

        if (! is_dir($tujuan) && ! mkdir($tujuan, 0775, true) && ! is_dir($tujuan)) {
            // Biarkan pemanggil yang menangani kegagalan lewat is_dir() berikutnya.
            return $tujuan;
        }

        return $tujuan;
    }
}

if (! function_exists('simpan_berkas')) {
    /**
     * Simpan berkas unggahan dengan nama acak.
     *
     * @param \CodeIgniter\HTTP\Files\UploadedFile|null $berkas
     * @param string                                    $folder  subfolder di writable/uploads
     * @param array{ext?: list<string>, mime?: list<string>, maks_kb?: int} $opsi
     *
     * @return array{ok: bool, nama: string|null, pesan: string|null}
     */
    function simpan_berkas($berkas, string $folder, array $opsi = []): array
    {
        $extDiizinkan  = $opsi['ext'] ?? ['jpg', 'jpeg', 'png'];
        $mimeDiizinkan = $opsi['mime'] ?? ['image/jpeg', 'image/png'];
        $maksKb        = $opsi['maks_kb'] ?? 2048;

        $gagal = static fn (string $pesan): array => ['ok' => false, 'nama' => null, 'pesan' => $pesan];

        if ($berkas === null || ! $berkas->isValid()) {
            return $gagal('Berkas tidak valid atau gagal diunggah.');
        }

        if ($berkas->hasMoved()) {
            return $gagal('Berkas sudah pernah dipindahkan.');
        }

        // Batas ukuran dihitung dari ukuran berkas yang benar-benar diterima.
        $ukuranKb = (int) ceil(((int) $berkas->getSize()) / 1024);
        if ($ukuranKb > $maksKb) {
            return $gagal('Ukuran berkas maksimal ' . (int) round($maksKb / 1024) . ' MB (berkas Anda ' . $ukuranKb . ' KB).');
        }

        // Deteksi tipe dari isi berkas (finfo), bukan dari MIME kiriman browser.
        $mimeAsli = (new \finfo(FILEINFO_MIME_TYPE))->file($berkas->getTempName()) ?: '';
        if (! in_array($mimeAsli, $mimeDiizinkan, true)) {
            return $gagal('Isi berkas tidak dikenali. Hanya diizinkan: ' . implode(', ', $extDiizinkan) . '.');
        }

        $ext = strtolower(pathinfo($berkas->getClientName(), PATHINFO_EXTENSION));
        if (! in_array($ext, $extDiizinkan, true)) {
            return $gagal('Ekstensi berkas harus salah satu dari: ' . implode(', ', $extDiizinkan) . '.');
        }

        $tujuan = folder_unggahan($folder);
        if (! is_dir($tujuan)) {
            return $gagal('Folder penyimpanan berkas tidak dapat dibuat.');
        }

        // getRandomName() memakai random_bytes() -> nama tidak bisa ditebak.
        $namaBaru = $berkas->getRandomName();

        if (! $berkas->move($tujuan, $namaBaru)) {
            return $gagal('Berkas gagal disimpan: ' . $berkas->getErrorString());
        }

        return ['ok' => true, 'nama' => $namaBaru, 'pesan' => null];
    }
}

if (! function_exists('path_berkas')) {
    /**
     * Path absolut sebuah berkas unggahan, atau null bila tidak aman/tidak ada.
     * Melindungi dari path traversal (mis. nama "../../.env").
     */
    function path_berkas(string $folder, ?string $nama): ?string
    {
        if ($nama === null || $nama === '') {
            return null;
        }

        // Nama harus nama berkas murni, tanpa folder.
        if ($nama !== basename($nama) || str_contains($nama, '..')) {
            return null;
        }

        $akar = realpath(rtrim(WRITEPATH, '/\\') . DIRECTORY_SEPARATOR . 'uploads');
        if ($akar === false) {
            return null;
        }

        $real = realpath($akar . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . $nama);
        if ($real === false || ! str_starts_with($real, $akar)) {
            return null;
        }

        return $real;
    }
}

if (! function_exists('hapus_berkas')) {
    /**
     * Hapus berkas unggahan. Aman dipanggil untuk berkas yang tidak ada.
     */
    function hapus_berkas(string $folder, ?string $nama): bool
    {
        $path = path_berkas($folder, $nama);

        return $path !== null && @unlink($path);
    }
}

if (! function_exists('url_berkas')) {
    /**
     * URL untuk menampilkan berkas unggahan lewat controller Berkas.
     */
    function url_berkas(string $folder, ?string $nama): ?string
    {
        if ($nama === null || $nama === '') {
            return null;
        }

        return base_url('berkas/' . rawurlencode($folder) . '/' . rawurlencode($nama));
    }
}
