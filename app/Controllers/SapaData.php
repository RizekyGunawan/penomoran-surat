<?php

namespace App\Controllers;

class SapaData extends BaseController
{
    public function index()
    {
        try {
            $client = \Config\Services::curlRequest();
            
            $response = $client->request('GET', 'https://sapa.kemenkopmk.go.id/api/public/unit-recap', [
                'headers' => [
                    'X-API-Key' => 'test_api_key_sapa_2025'
                ]
            ]);
            
            $data = json_decode($response->getBody(), true);
            
            return view('sapa_data', ['data' => $data]);
        } catch (\Exception $e) {
            return view('sapa_data', [
                'error' => 'Gagal mengambil data: ' . $e->getMessage()
            ]);
        }
    }
}
