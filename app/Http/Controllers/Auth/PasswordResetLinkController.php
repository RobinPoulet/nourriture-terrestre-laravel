<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

/**
 * « Mot de passe oublié » : envoi d'un lien pour en choisir un nouveau
 */
class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']], [
            'email.required' => 'Merci de renseigner ton email',
            'email.email' => "L'email n'est pas valide",
        ]);

        Password::sendResetLink($request->only('email'));

        // Même réponse que le compte existe ou non : on ne révèle pas les emails enregistrés
        return back()->with('success', "Si un compte existe pour cet email, un lien vient d'y être envoyé.");
    }
}
