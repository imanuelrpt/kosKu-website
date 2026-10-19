<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * BaseController - tempat mendaftarkan helper/library yang dipakai
 * oleh semua controller aplikasi (admin & penghuni).
 */
abstract class BaseController extends Controller
{
    /**
     * Helper yang otomatis tersedia di seluruh controller.
     * - kost     : format rupiah, tanggal Indonesia, badge status
     * - upload   : simpan/hapus/serve berkas unggahan
     * - form     : form_open(), validation_list_errors()
     * - url      : base_url(), site_url(), anchor()
     * - security : csrf_token(), csrf_field()
     *
     * @var list<string>
     */
    protected $helpers = ['kost', 'auth', 'setting', 'upload', 'form', 'url', 'security'];

    /**
     * Data global yang selalu dikirim ke view (nama aplikasi, dsb).
     *
     * @var array<string, mixed>
     */
    protected array $viewData = [
        'appName' => 'KosKu',
    ];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
    }

    /**
     * Render view sambil menyertakan data global.
     *
     * @param array<string, mixed> $data
     */
    protected function render(string $view, array $data = []): string
    {
        return view($view, array_merge($this->viewData, $data));
    }
}
