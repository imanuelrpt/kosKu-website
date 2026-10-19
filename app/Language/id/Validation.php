<?php

/**
 * Pesan validasi Bahasa Indonesia.
 *
 * {field} akan diganti label field, {param} diganti parameter aturan,
 * dan {value} diganti nilai yang diisi pengguna.
 */
return [
    // Pesan inti
    'noRuleSets'      => 'Tidak ada set aturan yang ditentukan pada konfigurasi Validasi.',
    'ruleNotFound'    => '"{0}" bukan aturan yang valid.',
    'groupNotFound'   => '"{0}" bukan grup aturan validasi.',
    'groupNotArray'   => '"{0}" grup aturan harus berupa array.',
    'invalidTemplate' => '"{0}" bukan template Validasi yang valid.',

    // Pesan aturan
    'alpha'                 => '{field} hanya boleh berisi huruf.',
    'alpha_dash'            => '{field} hanya boleh berisi huruf, angka, garis bawah, dan tanda hubung.',
    'alpha_numeric'         => '{field} hanya boleh berisi huruf dan angka.',
    'alpha_numeric_punct'   => '{field} hanya boleh berisi huruf, angka, spasi, dan karakter ~ ! # $ % & * - _ + = | : .',
    'alpha_numeric_space'   => '{field} hanya boleh berisi huruf, angka, dan spasi.',
    'alpha_space'           => '{field} hanya boleh berisi huruf dan spasi.',
    'decimal'               => '{field} harus berisi angka desimal.',
    'differs'               => '{field} harus berbeda dengan {param}.',
    'equals'                => '{field} harus sama persis dengan: {param}.',
    'exact_length'          => '{field} harus tepat {param} karakter.',
    'field_exists'          => '{field} harus ada.',
    'greater_than'          => '{field} harus berisi angka lebih besar dari {param}.',
    'greater_than_equal_to' => '{field} harus berisi angka lebih besar atau sama dengan {param}.',
    'hex'                   => '{field} hanya boleh berisi karakter heksadesimal.',
    'in_list'               => '{field} harus salah satu dari: {param}.',
    'integer'               => '{field} harus berisi bilangan bulat.',
    'is_natural'            => '{field} hanya boleh berisi angka.',
    'is_natural_no_zero'    => '{field} hanya boleh berisi angka dan harus lebih besar dari nol.',
    'is_not_unique'         => '{field} harus berisi nilai yang sudah ada di database.',
    'is_unique'             => '{field} sudah digunakan, silakan pakai nilai lain.',
    'less_than'             => '{field} harus berisi angka lebih kecil dari {param}.',
    'less_than_equal_to'    => '{field} harus berisi angka lebih kecil atau sama dengan {param}.',
    'matches'               => '{field} tidak sama dengan {param}.',
    'max_length'            => '{field} tidak boleh lebih dari {param} karakter.',
    'min_length'            => '{field} minimal {param} karakter.',
    'not_equals'            => '{field} tidak boleh bernilai: {param}.',
    'not_in_list'           => '{field} tidak boleh salah satu dari: {param}.',
    'numeric'               => '{field} hanya boleh berisi angka.',
    'regex_match'           => 'Format {field} tidak sesuai.',
    'required'              => '{field} wajib diisi.',
    'required_with'         => '{field} wajib diisi bila {param} juga diisi.',
    'required_without'      => '{field} wajib diisi bila {param} tidak diisi.',
    'string'                => '{field} harus berupa teks yang valid.',
    'timezone'              => '{field} harus berupa zona waktu yang valid.',
    'valid_base64'          => '{field} harus berupa string base64 yang valid.',
    'valid_email'           => '{field} harus berisi alamat email yang valid.',
    'valid_emails'          => '{field} harus berisi semua alamat email yang valid.',
    'valid_ip'              => '{field} harus berisi alamat IP yang valid.',
    'valid_url'             => '{field} harus berisi URL yang valid.',
    'valid_url_strict'      => '{field} harus berisi URL yang valid.',
    'valid_date'            => '{field} harus berisi tanggal yang valid.',
    'valid_json'            => '{field} harus berisi JSON yang valid.',

    // Kartu kredit
    'valid_cc_num' => '{field} bukan nomor kartu kredit yang valid.',

    // Berkas unggahan
    'uploaded' => '{field} bukan berkas unggahan yang valid.',
    'max_size' => 'Ukuran {field} terlalu besar.',
    'is_image' => '{field} bukan berkas gambar yang valid.',
    'mime_in'  => 'Tipe berkas {field} tidak diizinkan.',
    'ext_in'   => 'Ekstensi berkas {field} tidak diizinkan.',
    'max_dims' => '{field} bukan gambar, atau ukurannya terlalu lebar/tinggi.',
    'min_dims' => '{field} bukan gambar, atau ukurannya kurang lebar/tinggi.',
];
