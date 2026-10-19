<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UserModel - akun login (admin & penghuni).
 * Password selalu di-hash memakai password_hash() lewat callback.
 */
class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'email', 'password', 'role', 'phone'];

    // Timestamps
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validasi
    protected $validationRules = [
        'name'     => ['label' => 'Nama', 'rules' => 'required|min_length[3]|max_length[100]'],
        'email'    => ['label' => 'Email', 'rules' => 'required|valid_email|max_length[150]|is_unique[users.email,id,{id}]'],
        'password' => ['label' => 'Password', 'rules' => 'permit_empty|min_length[6]|max_length[100]'],
        'role'     => ['label' => 'Peran', 'rules' => 'required|in_list[admin,penghuni]'],
        'phone'    => ['label' => 'Nomor HP', 'rules' => 'permit_empty|max_length[30]'],
    ];

    protected $validationMessages = [
        'name' => [
            'required'   => 'Nama wajib diisi.',
            'min_length' => 'Nama minimal {param} karakter.',
        ],
        'email' => [
            'required'    => 'Email wajib diisi.',
            'valid_email' => 'Format email tidak valid.',
            'is_unique'   => 'Email {value} sudah terdaftar.',
        ],
        'password' => [
            'min_length' => 'Password minimal {param} karakter.',
        ],
    ];

    protected $skipValidation = false;

    // Callback: hash password sebelum simpan/update.
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data): array
    {
        if (isset($data['data']['password']) && $data['data']['password'] !== '') {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        } else {
            // Jangan timpa password lama dengan nilai kosong.
            unset($data['data']['password']);
        }

        return $data;
    }

    /**
     * Cari user berdasarkan email (user yang sudah di-soft-delete dikecualikan).
     */
    public function cariEmail(string $email): ?array
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Data ringkas untuk disimpan di session (tanpa password).
     *
     * @param array<string, mixed> $user
     *
     * @return array<string, mixed>
     */
    public static function untukSession(array $user): array
    {
        return [
            'id'    => (int) $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role'],
            'phone' => $user['phone'] ?? null,
        ];
    }
}
