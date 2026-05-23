<?php

namespace App\Models;

use CodeIgniter\Model;

class JenisNaskahModel extends Model
{
    protected $table            = 'jenis_naskah';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nama_jenis', 'is_active'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'nama_jenis' => 'required|max_length[100]|is_unique[jenis_naskah.nama_jenis,id,{id}]',
    ];
    protected $validationMessages   = [
        'nama_jenis' => [
            'required' => 'Nama jenis naskah harus diisi',
            'max_length' => 'Maksimal 100 karakter',
            'is_unique' => 'Nama jenis naskah ini sudah ada di database',
        ],
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
