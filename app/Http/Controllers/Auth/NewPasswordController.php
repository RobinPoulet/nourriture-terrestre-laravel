<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Choix du mot de passe depuis un lien reçu par email (invitation ou mot de passe oublié), puis connexion
 */
class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => (string) $request->query('email', '')]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'email.required' => 'Merci de renseigner ton email',
            'email.email' => "L'email n'est pas valide",
            'password.required' => 'Merci de choisir un mot de passe',
            'password.confirmed' => 'Les deux mots de passe ne correspondent pas',
            'password.min' => 'Le mot de passe doit faire au moins :min caractères',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));
                Auth::login($user, remember: true);
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withErrors(['email' => "Ce lien n'est plus valide (ou l'email ne correspond pas) : demandes-en un nouveau"])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('home')->with('success', 'Mot de passe enregistré, bienvenue '.Auth::user()->name.' !');
    }
}
