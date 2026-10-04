<?php

namespace App\Http\Controllers;

use App\Enums\SettingKey;
use App\Models\Announcement;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\SetPasswordNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Page principale de l'administration
     */
    public function index(): View
    {
        $settings = [];
        foreach (SettingKey::cases() as $key) {
            $settings[$key->value] = Setting::getValue($key);
        }

        return view('admin.index', [
            'users' => User::query()->orderBy('name')->get(),
            'announcements' => Announcement::all(),
            'settings' => $settings,
        ]);
    }

    // ── Utilisateurs ──────────────────────────────────────────────

    /**
     * Créer un utilisateur (non admin) ; il pourra se connecter une fois l'invitation acceptée
     */
    public function createUser(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);
        if (is_string($validated)) {
            return $this->back(error: $validated);
        }

        $user = User::query()->create([...$validated, 'is_admin' => false, 'creation_date' => now()->toDateString()]);

        return $this->back("L'utilisateur $user->name a bien été créé");
    }

    /**
     * Modifier le nom et l'email d'un utilisateur
     */
    public function editUser(Request $request, User $user): RedirectResponse
    {
        $validated = $this->validateUser($request, $user);
        if (is_string($validated)) {
            return $this->back(error: $validated);
        }

        $user->update($validated);

        return $this->back("L'utilisateur $user->name a bien été modifié");
    }

    public function deleteUser(User $user): RedirectResponse
    {
        $user->delete();

        return $this->back("L'utilisateur $user->name a bien été supprimé");
    }

    /**
     * Basculer le rôle admin d'un utilisateur
     */
    public function toggleUserRole(User $user): RedirectResponse
    {
        $user->update(['is_admin' => ! $user->is_admin]);
        $label = $user->is_admin ? 'promu administrateur' : 'rétrogradé utilisateur';

        return $this->back("$user->name a été $label");
    }

    /**
     * Envoie par email un lien pour choisir son mot de passe ; le lien est aussi affiché à l'admin
     * pour pouvoir le transmettre autrement (messagerie…) si l'email n'arrive pas
     */
    public function inviteUser(User $user): RedirectResponse
    {
        if ($user->email === null) {
            return $this->back(error: "Renseigne d'abord l'email de $user->name");
        }

        $token = Password::broker()->createToken($user);
        $user->notify(new SetPasswordNotification($token, isInvitation: true));

        return $this->back("Invitation envoyée à $user->email")
            ->with('invitationLink', SetPasswordNotification::url($user, $token));
    }

    // ── Paramètres ────────────────────────────────────────────────

    public function updateSettings(Request $request): RedirectResponse
    {
        foreach ([SettingKey::TickerDisabled, SettingKey::ForceOpenForm, SettingKey::ForceOpenVote] as $key) {
            Setting::setValue($key, $request->input($key->value) === '1' ? '1' : '0');
        }

        $smsSendTime = (string) $request->input(SettingKey::SmsSendTime->value, '');
        if (preg_match('/^\d{2}:\d{2}$/', $smsSendTime)) {
            Setting::setValue(SettingKey::SmsSendTime, $smsSendTime);
        }

        return $this->back('Paramètres mis à jour');
    }

    // ── Annonces ticker ───────────────────────────────────────────

    public function createAnnouncement(Request $request): RedirectResponse
    {
        $message = trim((string) $request->input('message', ''));
        if ($message === '') {
            return $this->back(error: 'Le message ne peut pas être vide');
        }

        Announcement::query()->create(['message' => $message, 'is_visible' => true]);

        return $this->back('Annonce ajoutée avec succès');
    }

    public function editAnnouncement(Request $request, Announcement $announcement): RedirectResponse
    {
        $message = trim((string) $request->input('message', ''));
        if ($message === '') {
            return $this->back(error: 'Le message ne peut pas être vide');
        }

        $announcement->update(['message' => $message]);

        return $this->back('Annonce modifiée avec succès');
    }

    public function deleteAnnouncement(Announcement $announcement): RedirectResponse
    {
        $announcement->delete();

        return $this->back('Annonce supprimée');
    }

    public function toggleAnnouncement(Announcement $announcement): RedirectResponse
    {
        $announcement->update(['is_visible' => ! $announcement->is_visible]);

        return $this->back($announcement->is_visible ? 'Annonce affichée' : 'Annonce masquée');
    }

    /**
     * Nom et email saisis, ou le message d'erreur
     *
     * @return array{name: string, email: ?string}|string
     */
    private function validateUser(Request $request, ?User $user = null): array|string
    {
        $name = trim((string) $request->input('name', ''));
        $email = Str::lower(trim((string) $request->input('email', ''))) ?: null;

        return match (true) {
            $name === '' => "Il faut un nom pour l'utilisateur",
            $email !== null && filter_var($email, FILTER_VALIDATE_EMAIL) === false => "L'email $email n'est pas valide",
            $email !== null && User::query()->where('email', $email)->when($user, fn ($query) => $query->whereKeyNot($user->id))->exists() => "L'email $email est déjà utilisé",
            default => ['name' => $name, 'email' => $email],
        };
    }

    /**
     * Retour à l'admin avec un message flash
     */
    private function back(?string $success = null, ?string $error = null): RedirectResponse
    {
        $redirect = redirect()->route('admin.index');

        return $error !== null ? $redirect->withErrors([$error]) : $redirect->with('success', $success);
    }
}
