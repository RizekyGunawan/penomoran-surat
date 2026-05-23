<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;

class AuditLogController extends BaseController
{
    public function index()
    {
        $auditModel = new AuditLogModel();
        
        $perPage = $this->request->getGet('per_page') ?? 25;
        $page    = $this->request->getGet('page') ?? 1;
        
        // Paginate logs natively, join dengan users untuk mendapatkan username
        $logs = $auditModel->select('audit_logs.*, users.username_ldap')
                           ->join('users', 'users.id = audit_logs.user_id', 'left')
                           ->orderBy('audit_logs.created_at', 'DESC')
                           ->paginate($perPage, 'default');
        
        $data = [
            'title' => 'Riwayat Audit Log',
            'logs'  => $logs,
            'pager' => $auditModel->pager,
            // Construct pseudo pagination array for partial compatibility with 'partials/pagination'
            'pagination' => [
                'current_page' => $auditModel->pager->getCurrentPage('default'),
                'total_pages'  => $auditModel->pager->getPageCount('default'),
                'total_records'=> $auditModel->pager->getTotal('default'),
                'per_page'     => $perPage
            ]
        ];

        return view('admin/audit_log/index', $data);
    }
}
