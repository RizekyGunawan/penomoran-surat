<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * AuditLogModel
 * Merekam jejak perubahan data penting di aplikasi.
 */
class AuditLogModel extends Model
{
    protected $table            = 'audit_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'user_id',
        'action',
        'entity_table',
        'entity_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = '';
    
    /**
     * Helper untuk mencatat log dengan cepat
     */
    public function logAction(string $action, string $table, string $id, ?array $oldData = null, ?array $newData = null)
    {
        $request = service('request');
        
        $userId = null;
        if (function_exists('session') && session()->has('user')) {
            $userId = session('user.id') ?? session('user.pegawai_id');
        }

        return $this->insert([
            'user_id'      => $userId,
            'action'       => $action,
            'entity_table' => $table,
            'entity_id'    => $id,
            'old_values'   => $oldData ? json_encode($oldData) : null,
            'new_values'   => $newData ? json_encode($newData) : null,
            'ip_address'   => $request->getIPAddress(),
            'user_agent'   => $request->getUserAgent() ? (string)$request->getUserAgent() : null,
        ]);
    }
}
