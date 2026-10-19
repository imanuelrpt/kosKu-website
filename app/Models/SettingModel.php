<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * SettingModel - pengaturan aplikasi berbentuk key-value.
 */
class SettingModel extends Model
{
    protected $table            = 'settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['setting_key', 'setting_value'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil seluruh pengaturan sebagai array key => value.
     *
     * @return array<string, string|null>
     */
    public function semua(): array
    {
        $hasil = [];
        foreach ($this->findAll() as $row) {
            $hasil[$row['setting_key']] = $row['setting_value'];
        }

        return $hasil;
    }

    /**
     * Simpan (insert/update) satu pengaturan.
     */
    public function setNilai(string $key, ?string $value): void
    {
        $row = $this->where('setting_key', $key)->first();

        if ($row !== null) {
            $this->update($row['id'], ['setting_value' => $value]);
        } else {
            $this->insert(['setting_key' => $key, 'setting_value' => $value]);
        }
    }
}
