<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\models\User;
use Illuminate\Support\Facades\Http;

class SsoController extends Controller
{
    public function login(Request $request)
    {
        $request->session()->put("state", $state = Str::random(40));
        $query = http_build_query([
            'client_id' => env('SSO_CLIENT_ID'),
            'redirect_uri' => env('SSO_REDIRECT_URI'),
            'response_type' => 'code',
            'scope' => '',
            'state' => $state,
        ]);
        return redirect("https://portal.hsu.go.id/oauth/authorize?{$query}");
    }

   public function callback(Request $request)
    {
        $state = $request->session()->pull('state');
        if ($state !== $request->state) {
            abort(403, 'Invalid state');
        }

        $tokenResponse = Http::asForm()->post('https://portal.hsu.go.id/oauth/token', [
            'client_id'     => env('SSO_CLIENT_ID'),
            'client_secret' => env('SSO_CLIENT_SECRET'),
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => env('SSO_REDIRECT_URI'),
            'code'          => $request->code,
        ]);

        if ($tokenResponse->failed()) {
            abort(500, 'Authentication failed at token endpoint');
        }

        $userResponse = Http::withHeaders([
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer '.$tokenResponse->json('access_token'),
        ])->get(env('SSO_BASE_URL').'/api/user');

        if ($userResponse->failed()) {
            abort(500, 'Failed to fetch user info from SSO');
        }

        $nik  = $userResponse->json('data.nik');
        $user = User::where('nik', $nik)->first();

        
       if (! $user) {
            // Revoke token (opsional, biar token langsung mati)
            $this->revokeTokenPortal($tokenResponse->json('access_token'));

            // Redirect user ke Portal logout agar session SSO ikut hilang
            $redirectBack = route('home');
            $portalLogout = env('SSO_BASE_URL')
                        . '/logout?redirect='
                        . urlencode($redirectBack);

            return redirect()->away($portalLogout)
                            ->with('swal', [
                                'title'    => 'Data anda tidak ditemukan di sistem kami.',
                                'icon'     => 'error',
                                'toast'    => true,
                                'position' => 'bottom-end',
                                'timer'    => 3000,
                            ]);
        }

        // login and then revoke the portal token
        Auth::login($user);
        $this->revokeTokenPortal($tokenResponse->json('access_token'));

        return redirect()->route('dashboard');
    }

    public function revokeTokenPortal($token)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ])->get(env('SSO_BASE_URL') .'/api/auth/logout');
        if($response){
            return response(['status' => 'success', 'message' => 'Logout sukses.']);
        }
        return response(['status' => 'error', 'message' => 'Logout gagal.']);
    }
}
