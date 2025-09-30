<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\models\User;
use Illuminate\Support\Facades\Hash;
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
            // Jika SSO memberikan data pengguna (mis. nik & nama) tetapi belum ada di DB,
            // kita buat akun sementara dengan role 'guest', login sebagai guest, lalu arahkan ke publik.
            $userData = $userResponse->json('data');

            if ($nik && $userData) {
                // Build minimal user payload. Sesuaikan field yang diperlukan.
                $name = $userData['nama'] ?? ($userData['name'] ?? 'Pengguna SSO');
                $email = $userData['email'] ?? null;

                // Create user record with a random password (hashed) so the account exists.
                $newUser = User::create([
                    'name' => $name,
                    'email' => $email,
                    'nik' => $nik,
                    'password' => Hash::make(Str::random(24)),
                ]);

                // Assign 'guest' role if using Spatie roles/permissions and the role exists.
                try {
                    if (method_exists($newUser, 'assignRole')) {
                        $newUser->assignRole('guest');
                    }
                } catch (\Exception $e) {
                    // If role doesn't exist or assign fails, ignore silently for now.
                }

                // Login the newly created guest user
                Auth::login($newUser);

                // Revoke token (opsional)
                $this->revokeTokenPortal($tokenResponse->json('access_token'));

                // Redirect to public home (or a dedicated guest landing) with a flash
                return redirect()->route('public.home')
                    ->with('swal', [
                        'title' => 'Anda masuk sebagai tamu (guest). Silakan lengkapi data jika perlu.',
                        'icon' => 'info',
                        'toast' => true,
                        'position' => 'bottom-end',
                        'timer' => 4000,
                    ])
                    ->with('guest_login', true);
            }

            // Jika tidak ada data pengguna dari SSO, lakukan revoke dan redirect ke portal logout
            $this->revokeTokenPortal($tokenResponse->json('access_token'));

            $redirectBack = route('public.home');
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

        // If the user does NOT have any admin-like role, treat them as public guest and redirect to public.home
        try {
            // If user explicitly has 'guest' role, send them to public.home immediately
            if (method_exists($user, 'hasRole') && $user->hasRole('guest')) {
                return redirect()->route('public.home');
            }

            if (method_exists($user, 'hasAnyRole')) {
                // If the user does NOT have admin/verifikator/user role, send to public
                if (! $user->hasAnyRole(['admin', 'verifikator', 'user'])) {
                    return redirect()->route('public.home');
                }
            }
        } catch (\Exception $e) {
            // If role checks fail for some reason, fall back to admin dashboard for safety.
        }

        return redirect()->route('admin.dashboard');
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
