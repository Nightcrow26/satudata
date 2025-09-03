<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LogOutController extends Controller
{
    /**d
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        Auth::logout();
        // 2) Redirect browser ke endpoint logout portal
        $redirectBack = route('home'); // route('home') mengembalikan URL home aplikasi
        $portalLogout = env('SSO_BASE_URL')
                      . '/logout?redirect=' 
                      . urlencode($redirectBack);
        return redirect()->away($portalLogout);
    }
}
