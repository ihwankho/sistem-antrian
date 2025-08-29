<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class LoketService
{
    public function getLokets($token)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json', // tambahkan ini
        ])->get('http://localhost:8000/api/lokets');

        return $response->json();
    }
}
