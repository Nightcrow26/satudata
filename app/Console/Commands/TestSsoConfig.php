<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TestSsoConfig extends Command
{
    protected $signature = 'sso:test';
    protected $description = 'Test SSO configuration';

    public function handle()
    {
        $this->info('Testing SSO Configuration...');
        
        // Test environment variables
        $clientId = env('SSO_CLIENT_ID');
        $clientSecret = env('SSO_CLIENT_SECRET');
        $redirectUri = env('SSO_REDIRECT_URI');
        $baseUrl = env('SSO_BASE_URL');

        $this->line("Client ID: " . ($clientId ? 'Set (' . substr($clientId, 0, 8) . '...)' : 'NOT SET'));
        $this->line("Client Secret: " . ($clientSecret ? 'Set (' . substr($clientSecret, 0, 8) . '...)' : 'NOT SET'));
        $this->line("Redirect URI: " . ($redirectUri ?: 'NOT SET'));
        $this->line("Base URL: " . ($baseUrl ?: 'NOT SET'));
        
        if (!$clientId || !$clientSecret || !$redirectUri || !$baseUrl) {
            $this->error('One or more SSO environment variables are not set.');
            return;
        }

        // Test if the authorization server is reachable
        try {
            $response = Http::timeout(10)->get($baseUrl);
            if ($response->successful()) {
                $this->info('✓ SSO Base URL is reachable');
            } else {
                $this->warn('⚠ SSO Base URL returned status: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error('✗ Cannot reach SSO Base URL: ' . $e->getMessage());
        }

        // Test authorization endpoint
        try {
            $authUrl = $baseUrl . '/oauth/authorize';
            $response = Http::timeout(10)->get($authUrl);
            $this->info('✓ Authorization endpoint status: ' . $response->status());
        } catch (\Exception $e) {
            $this->warn('⚠ Cannot reach authorization endpoint: ' . $e->getMessage());
        }

        // Test token endpoint (this will fail but we can see the error)
        try {
            $tokenUrl = $baseUrl . '/oauth/token';
            $response = Http::asForm()->post($tokenUrl, [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
                'code' => 'test_code'
            ]);
            
            $this->line('Token endpoint response: ' . $response->status());
            $this->line('Response body: ' . $response->body());
            
        } catch (\Exception $e) {
            $this->warn('Token endpoint error: ' . $e->getMessage());
        }
        
        $this->info('SSO configuration test completed.');
    }
}