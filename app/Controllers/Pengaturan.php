<?php

namespace App\Controllers;

use App\Models\SettingModel;

/**
 * Pengaturan aplikasi (khusus admin).
 */
class Pengaturan extends BaseController
{
    public function index(): string
    {
        return $this->render('pengaturan/index', [
            'title' => 'Pengaturan',
        ]);
    }

    /**
     * Simpan pengaturan.
     */
    public function simpan()
    {
        $model = model(SettingModel::class);

        // Data identitas kos (dipakai pada tagihan & kwitansi).
        $model->setNilai('app_name', trim((string) $this->request->getPost('app_name')) ?: 'KosKu');
        $model->setNilai('kos_name', trim((string) $this->request->getPost('kos_name')) ?: 'KosKu');
        $model->setNilai('kos_address', trim((string) $this->request->getPost('kos_address')));
        $model->setNilai('kos_phone', trim((string) $this->request->getPost('kos_phone')));
        $model->setNilai('kos_owner', trim((string) $this->request->getPost('kos_owner')));
        $model->setNilai('kos_bank', trim((string) $this->request->getPost('kos_bank')));

        // Tanggal jatuh tempo (1-28 supaya valid di semua bulan).
        $dueDay = (int) $this->request->getPost('invoice_due_day');
        $model->setNilai('invoice_due_day', (string) max(1, min(28, $dueDay > 0 ? $dueDay : 10)));

        // Checkbox: tidak terkirim bila tidak dicentang.
        $model->setNilai('registration_enabled', $this->request->getPost('registration_enabled') ? '1' : '0');

        return redirect()->to(base_url('pengaturan'))->with('sukses', 'Pengaturan berhasil disimpan.');
    }
}
