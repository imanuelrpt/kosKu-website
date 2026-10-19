<?php

/**
 * Helper pengaturan aplikasi.
 *
 * Dipakai di controller & view: setting('registration_enabled')
 *
 * Catatan: file helper di-include pada scope global, jadi nama kelas
 * harus ditulis lengkap (fully qualified) dan tidak boleh memakai `use`
 * untuk nama non-compound.
 */

if (! function_exists('setting')) {
    /**
     * Ambil nilai pengaturan dari tabel settings (di-cache per request).
     *
     * @param mixed $default
     *
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        // Cache statis supaya tidak query berulang dalam satu request.
        static $cache = null;

        if ($cache === null) {
            $cache = [];

            try {
                $cache = model(\App\Models\SettingModel::class)->semua();
            } catch (\Throwable $e) {
                // Tabel belum ada (mis. sebelum migrate) - pakai nilai default.
                $cache = [];
            }
        }

        return $cache[$key] ?? $default;
    }
}

if (! function_exists('setting_aktif')) {
    /**
     * Cek pengaturan boolean ("1", "true", "on" dianggap aktif).
     */
    function setting_aktif(string $key, bool $default = false): bool
    {
        $nilai = setting($key, $default ? '1' : '0');

        return in_array(strtolower((string) $nilai), ['1', 'true', 'on', 'ya'], true);
    }
}
