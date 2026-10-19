<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ---------------------------------------------------------------
// Publik
// ---------------------------------------------------------------
// "/" mengarahkan ke login atau beranda sesuai status login.
$routes->get('/', 'Home::index');

// Halaman verifikasi environment & preview layout (hanya development).
$routes->get('cek-sistem', 'Home::cekSistem');
$routes->get('preview/(:segment)', 'Home::preview/$1');

// ---------------------------------------------------------------
// Tamu (belum login)
// ---------------------------------------------------------------
$routes->group('', ['filter' => 'guest'], static function (RouteCollection $routes): void {
    $routes->get('login', 'Auth::login');
    $routes->post('login', 'Auth::attemptLogin');
    $routes->get('register', 'Auth::register');
    $routes->post('register', 'Auth::attemptRegister');
});

// ---------------------------------------------------------------
// Butuh login (admin maupun penghuni)
// ---------------------------------------------------------------
$routes->group('', ['filter' => 'auth'], static function (RouteCollection $routes): void {
    $routes->get('logout', 'Auth::logout');

    // Profil & ganti password - layout menyesuaikan peran.
    $routes->get('profil', 'Profil::index');
    $routes->post('profil', 'Profil::update');
    $routes->post('profil/password', 'Profil::updatePassword');
});

// ---------------------------------------------------------------
// Area admin
// ---------------------------------------------------------------
$routes->group('', ['filter' => 'role:admin'], static function (RouteCollection $routes): void {
    $routes->get('dashboard', 'Dashboard::index');

    $routes->get('pengaturan', 'Pengaturan::index');
    $routes->post('pengaturan/simpan', 'Pengaturan::simpan');

    // Data kamar
    $routes->get('kamar', 'Kamar::index');
    $routes->get('kamar/tambah', 'Kamar::create');
    $routes->post('kamar/simpan', 'Kamar::store');
    $routes->get('kamar/ubah/(:num)', 'Kamar::edit/$1');
    $routes->post('kamar/ubah/(:num)', 'Kamar::update/$1');
    $routes->post('kamar/hapus/(:num)', 'Kamar::delete/$1');

    // Data penghuni
    $routes->get('penghuni', 'Penghuni::index');
    $routes->get('penghuni/tambah', 'Penghuni::create');
    $routes->post('penghuni/simpan', 'Penghuni::store');
    $routes->get('penghuni/ubah/(:num)', 'Penghuni::edit/$1');
    $routes->post('penghuni/ubah/(:num)', 'Penghuni::update/$1');
    $routes->post('penghuni/hapus/(:num)', 'Penghuni::delete/$1');
    $routes->get('penghuni/(:num)', 'Penghuni::show/$1');

    // Sewa / check-in & check-out
    $routes->get('sewa', 'Sewa::index');
    $routes->get('sewa/checkin', 'Sewa::checkin');
    $routes->post('sewa/checkin', 'Sewa::checkinSimpan');
    $routes->get('sewa/checkout/(:num)', 'Sewa::checkout/$1');
    $routes->post('sewa/checkout/(:num)', 'Sewa::checkoutSimpan/$1');
    $routes->post('sewa/batal/(:num)', 'Sewa::batal/$1');

    // Tagihan
    $routes->get('tagihan', 'Tagihan::index');
    $routes->get('tagihan/buat', 'Tagihan::create');
    $routes->post('tagihan/buat', 'Tagihan::store');
    $routes->post('tagihan/generate', 'Tagihan::generate');
    $routes->get('tagihan/cetak/(:num)', 'Tagihan::cetak/$1');
    $routes->get('tagihan/(:num)', 'Tagihan::show/$1');
    $routes->post('tagihan/hapus/(:num)', 'Tagihan::delete/$1');

    // Pembayaran
    $routes->get('pembayaran', 'Pembayaran::index');
    $routes->get('pembayaran/catat', 'Pembayaran::create');
    $routes->post('pembayaran/catat', 'Pembayaran::store');
    $routes->get('pembayaran/kwitansi/(:num)', 'Pembayaran::kwitansi/$1');
    $routes->post('pembayaran/hapus/(:num)', 'Pembayaran::delete/$1');

    // Pengeluaran
    $routes->get('pengeluaran', 'Pengeluaran::index');
    $routes->get('pengeluaran/tambah', 'Pengeluaran::create');
    $routes->post('pengeluaran/simpan', 'Pengeluaran::store');
    $routes->get('pengeluaran/ubah/(:num)', 'Pengeluaran::edit/$1');
    $routes->post('pengeluaran/ubah/(:num)', 'Pengeluaran::update/$1');
    $routes->post('pengeluaran/hapus/(:num)', 'Pengeluaran::delete/$1');

    // Laporan
    $routes->get('laporan', 'Laporan::index');
    $routes->get('laporan/ekspor/(:segment)', 'Laporan::ekspor/$1');
});

// ---------------------------------------------------------------
// Area penghuni
// ---------------------------------------------------------------
$routes->group('', ['filter' => 'role:penghuni'], static function (RouteCollection $routes): void {
    $routes->get('penghuni/beranda', 'Penghuni\Beranda::index');

    // Tagihan milik penghuni (halaman spesifik didaftarkan lebih dulu).
    $routes->get('penghuni/tagihan', 'Penghuni\Tagihan::index');
    $routes->get('penghuni/tagihan/cetak/(:num)', 'Penghuni\Tagihan::cetak/$1');
    $routes->get('penghuni/tagihan/kwitansi/(:num)', 'Penghuni\Tagihan::kwitansi/$1');
    $routes->get('penghuni/tagihan/(:num)', 'Penghuni\Tagihan::show/$1');

    // Profil penghuni (ubah data diri & ganti password).
    $routes->get('penghuni/profil', 'Penghuni\Profil::index');
    $routes->post('penghuni/profil', 'Penghuni\Profil::update');
    $routes->post('penghuni/profil/password', 'Penghuni\Profil::updatePassword');
});

// ---------------------------------------------------------------
// Berkas unggahan (foto penghuni, bukti transfer)
// Tidak disajikan langsung dari folder publik - harus login dulu.
// ---------------------------------------------------------------
$routes->get('berkas/(:segment)/(:segment)', 'Berkas::kirim/$1/$2', ['filter' => 'auth']);
