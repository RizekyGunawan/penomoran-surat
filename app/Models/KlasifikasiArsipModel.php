<?php

namespace App\Models;

use CodeIgniter\Model;

class KlasifikasiArsipModel extends Model
{
    protected $table            = 'klasifikasi_arsip';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['kode', 'kode_induk', 'uraian', 'fungsi', 'is_active'];

    // Validation
    protected $validationRules      = [
        'kode' => 'required|max_length[20]|is_unique[klasifikasi_arsip.kode,id,{id}]',
        'kode_induk' => 'permit_empty|max_length[20]',
        'fungsi' => 'required|in_list[fasilitatif,substantif]',
    ];
    protected $validationMessages   = [
        'kode' => [
            'required' => 'Kode klasifikasi harus diisi',
            'is_unique' => 'Kode klasifikasi ini sudah ada di database',
        ]
    ];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;
}
