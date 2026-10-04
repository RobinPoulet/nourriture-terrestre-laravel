<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Authentification admin : nom + mot de passe, en session
 */
class AdminLoginController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::user()?->is_admin) {
            return redirect()->route('admin.index');
        }

        return view('admin.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = [
            'name' => trim((string) $request->input('name', '')),
            'password' => (string) $request->input('password', ''),
            'is_admin' => 1,
        ];

        if ($credentials['name'] !== '' && $credentials['password'] !== '' && Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->route('admin.index');
        }

        // Ralentit les tentatives de force brute
        sleep(1);

        return redirect()->route('admin.login')->withErrors(['Identifiants invalides']);
    }

    public function destroy(Request $request): Response
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
