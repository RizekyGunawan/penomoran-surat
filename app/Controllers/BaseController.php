<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.

        // E.g.: $this->session = service('session');
    }

    // ─────────────────────────────────────────────────────────
    // Shared Helpers
    // ─────────────────────────────────────────────────────────

    /**
     * Cari data user (termasuk nama aslinya) yang membatalkan sebuah surat.
     *
     * Menggantikan ~60 baris duplikasi yang sebelumnya ada di
     * Penomoran::detail() dan PenomoranAdmin::detail().
     *
     * Urutan pencarian:
     *  1. Tabel users (kandidat: users, ms_users, tbl_users, m_users, user)
     *  2. Jika ditemukan tapi nama kosong dan ada pegawai_id,
     *     cari nama asli di tabel pegawai (kandidat: pegawai, ms_pegawai, …)
     *
     * @param  int|string $cancelledById  Nilai kolom DIBATALKAN_OLEH
     * @return object|null                Object user dengan property `name`, atau null
     */
    protected function getCancelledByUser($cancelledById): ?object
    {
        if (empty($cancelledById)) {
            return null;
        }

        $db = \Config\Database::connect();

        try {
            // 1. Cari record user
            $userRecord = null;
            $userTables = ['users', 'ms_users', 'tbl_users', 'm_users', 'user'];

            foreach ($userTables as $tbl) {
                if ($db->tableExists($tbl)) {
                    $userRecord = $db->table($tbl)
                                     ->where('id', $cancelledById)
                                     ->get()
                                     ->getRow();
                    if ($userRecord) {
                        break;
                    }
                }
            }

            if (!$userRecord) {
                return null;
            }

            // 2. Jika nama sudah ada di record user, selesai
            if (!empty($userRecord->name)) {
                return $userRecord;
            }

            // Normalisasi kolom 'nama' → 'name'
            if (!empty($userRecord->nama)) {
                $userRecord->name = $userRecord->nama;
                return $userRecord;
            }

            // 3. Jika ada pegawai_id, cari nama di tabel pegawai
            if (!empty($userRecord->pegawai_id)) {
                $pegawaiTables = [
                    'pegawai', 'ms_pegawai', 'tbl_pegawai',
                    'm_pegawai', 'employees', 'pegawai_master', 'pegawai_view',
                ];
                $nameColumns = ['nama', 'name', 'full_name', 'nama_pegawai', 'nama_lengkap'];

                foreach ($pegawaiTables as $ptbl) {
                    if (!$db->tableExists($ptbl)) {
                        continue;
                    }

                    $pBuilder = $db->table($ptbl);

                    // pegawai_view bisa memakai id atau pegawai_id
                    $pegawaiRow = ($ptbl === 'pegawai_view')
                        ? $pBuilder->where('id', $userRecord->pegawai_id)
                                   ->orWhere('pegawai_id', $userRecord->pegawai_id)
                                   ->get(1)->getRow()
                        : $pBuilder->where('id', $userRecord->pegawai_id)
                                   ->get()->getRow();

                    if (!$pegawaiRow) {
                        continue;
                    }

                    foreach ($nameColumns as $col) {
                        if (!empty($pegawaiRow->$col)) {
                            $userRecord->name = $pegawaiRow->$col;
                            break 2; // keluar dari kedua foreach
                        }
                    }
                }
            }

            // Fallback: kembalikan record apa adanya (misal hanya username)
            return $userRecord;

        } catch (\Throwable $e) {
            log_message('error', '[BaseController::getCancelledByUser] ' . $e->getMessage());
            return null;
        }
    }
}
