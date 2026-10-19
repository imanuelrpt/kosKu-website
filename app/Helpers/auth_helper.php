<?php

/**
 * Helper autentikasi.
 *
 * Data user yang login disimpan di session dengan kunci "user".
 */

if (! function_exists('user_login')) {
    /**
     * Data user yang sedang login, atau null bila belum login.
     *
     * @return array<string, mixed>|null
     */
    function user_login(): ?array
    {
        $user = session()->get('user');

        return is_array($user) && isset($user['id']) ? $user : null;
    }
}

if (! function_exists('user_id')) {
    /**
     * ID user yang sedang login.
     */
    function user_id(): ?int
    {
        return user_login()['id'] ?? null;
    }
}

if (! function_exists('user_role')) {
    /**
     * Peran user yang sedang login: "admin", "penghuni", atau null.
     */
    function user_role(): ?string
    {
        return user_login()['role'] ?? null;
    }
}

if (! function_exists('is_admin')) {
    function is_admin(): bool
    {
        return user_role() === 'admin';
    }
}

if (! function_exists('is_penghuni')) {
    function is_penghuni(): bool
    {
        return user_role() === 'penghuni';
    }
}

if (! function_exists('beranda_peran')) {
    /**
     * URL tujuan setelah login / setelah redirect karena peran.
     */
    function beranda_peran(?string $role = null): string
    {
        $role ??= user_role();

        return $role === 'penghuni' ? base_url('penghuni/beranda') : base_url('dashboard');
    }
}

if (! function_exists('penghuni_login')) {
    /**
     * Data penghuni (tabel tenants) yang tertaut ke akun yang sedang login.
     *
     * Akun berperan penghuni boleh saja belum tertaut ke data penghuni
     * (mis. baru mendaftar sendiri), sehingga null adalah keadaan yang wajar.
     *
     * @return array<string, mixed>|null
     */
    function penghuni_login(): ?array
    {
        $uid = user_id();

        if ($uid === null) {
            return null;
        }

        return (new \App\Models\TenantModel())->cariLewatAkun($uid);
    }
}

if (! function_exists('layout_peran')) {
    /**
     * Nama layout yang dipakai sesuai peran user yang sedang login.
     */
    function layout_peran(?string $role = null): string
    {
        $role ??= user_role();

        return $role === 'penghuni' ? 'layouts/tenant' : 'layouts/admin';
    }
}
