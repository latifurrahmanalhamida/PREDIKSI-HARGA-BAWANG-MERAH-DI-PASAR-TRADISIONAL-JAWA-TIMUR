<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class FastApiService
{
    private string $baseUrl;
    private string $token;

    public function __construct()
    {
        $this->baseUrl = config('app.fastapi_base_url', env('FASTAPI_BASE_URL', 'http://127.0.0.1:8000'));
        $this->token = env('FASTAPI_TOKEN', 'changeme-secret-token');
    }

    public function getHealth()
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl .  '/health');
            return $response->successful() ? $response->json() : null;
        } catch (RequestException $e) {
            return null;
        }
    }

    public function predictNext(string $region, array $window)
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $this->token,
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/predict/next', [
                    'region' => $region,
                    'window' => $window
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['detail'] ??  'Unknown error',
                'status' => $response->status()
            ];
        } catch (RequestException $e) {
            return [
                'success' => false,
                'error' => 'Connection failed: ' . $e->getMessage(),
                'status' => 500
            ];
        }
    }

    public function getAvailableRegions()
    {
        $health = $this->getHealth();
        return $health['regions_loaded'] ?? [];
    }
}