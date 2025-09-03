<?php

namespace App\Library;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class SikonSplpLibrary
{
    protected $baseUriOauth2;
    protected $baseUriSplp;
    protected $consumerKey;
    protected $consumerSecret;

    public function __construct()
    {
        $this->baseUriOauth2    = config('services.api.splp_oauth_uri');
        $this->baseUriSplp      = config('services.api.splp_base_uri');
        $this->consumerKey      = config('services.api.splp_consumer_key');
        $this->consumerSecret   = config('services.api.splp_consumer_secret');
    }

    public function getToken()
    {
        $encryptedTokenSplp = Cache::get('access_token_splp');
        $tokenTypeSplp = Cache::get('token_type_splp');

        if (!$encryptedTokenSplp || !$tokenTypeSplp) {
            $tokenSplp = $this->requestTokenOauth();

            if (!isset($tokenSplp['access_token']) || !isset($tokenSplp['token_type'])) {
                Log::error('Gagal mendapatkan token OAuth: ', $tokenSplp);
                return null;
            }

            $encryptedToken = Crypt::encryptString($tokenSplp['access_token']);
            Cache::put('access_token_splp', $encryptedToken, now()->addSeconds($tokenSplp['expires_in']));
            Cache::put('token_type_splp', $tokenSplp['token_type'], now()->addSeconds($tokenSplp['expires_in']));

            return [
                'token_splp'      => $tokenSplp['access_token'],
                'token_type_splp' => $tokenSplp['token_type']
            ];
        }

        return [
            'token_splp'      => Crypt::decryptString($encryptedTokenSplp),
            'token_type_splp' => $tokenTypeSplp
        ];
    }

    public function requestTokenOauth()
    {
        try {
            $response = Http::asForm()->withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept'       => 'application/json',
            ])->post($this->baseUriOauth2, [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->consumerKey,
                'client_secret' => $this->consumerSecret,
            ]);

            if ($response->failed()) {
                Log::error('Request token OAuth gagal: ' . $response->body());
                return [];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Exception saat request token OAuth: ' . $e->getMessage());
            return [];
        }
    }

    public function makeApiRequest($method, $endpoint, $params = [], $headers = [], $timeout = 30)
    {
        $token = $this->getToken();

        if (!$token) {
            Log::error('Gagal mendapatkan token untuk request API.');
            return ['error' => 'Gagal mendapatkan token'];
        }

        $maxAttempts = 4;
        $attempt = 0;

        do {
            try {
                $response = Http::withHeaders(array_merge([
                    'Content-Type'  => 'application/x-www-form-urlencoded',
                    'Accept'        => 'application/json',
                    'Authorization' => $token['token_type_splp'] . ' ' . $token['token_splp'],
                ], $headers))->timeout($timeout)->{$method}($this->baseUriSplp . '/' . $endpoint, $params);

                // Jika response sukses
                if ($response->successful()) {
                    if (isset($headers['Accept']) && $headers['Accept'] === 'application/pdf') {
                        return $response->body();
                    }
                    return $response->json();
                }

                // Jika gagal tapi bukan 404, hentikan loop
                if ($response->status() !== 404) {
                    Log::error("API Request Gagal ($method $endpoint): " . $response->body());
                    return $response->json();
                }

                // Delay sebelum retry (opsional, 0.5 detik)
                usleep(500000);
            } catch (\Exception $e) {
                Log::error("Exception saat request API ($method $endpoint): " . $e->getMessage());
                return ['error' => 'Terjadi kesalahan saat menghubungi API'];
            }

            $attempt++;
        } while ($attempt < $maxAttempts);

        return ['error' => "API gagal setelah $maxAttempts percobaan (kemungkinan lazy-load masih aktif)."];
    }

    public function clearCache()
    {
        Cache::flush();
    }
}
