<?php

namespace App\Http\Controllers;

use App\Enums\SettingKey;
use App\Models\Announcement;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
     * Créer un utilisateur (non admin)
     */
    public function createUser(Request $request): RedirectResponse
    {
        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            return $this->back(error: "Il faut un nom pour l'utilisateur");
        }

        $user = User::query()->create(['name' => $name, 'is_admin' => false, 'creation_date' => now()->toDateString()]);

        return $this->back("L'utilisateur $user->name a bien été créé");
    }

    /**
     * Modifier le nom d'un utilisateur
     */
    public function editUser(Request $request, User $user): RedirectResponse
    {
        $name = trim((string) $request->input('name', ''));
        if ($name === '') {
            return $this->back(error: "Il faut un nom pour l'utilisateur");
        }

        $user->update(['name' => $name]);

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
     * Dissocie l'appareil d'un utilisateur (il pourra se ré-identifier en passant une commande)
     */
    public function resetUserDevice(User $user): RedirectResponse
    {
        $user->update(['cookie_hash' => null]);

        return $this->back("L'appareil de $user->name a été réinitialisé");
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
     * Retour à l'admin avec un message flash
     */
    private function back(?string $success = null, ?string $error = null): RedirectResponse
    {
        $redirect = redirect()->route('admin.index');

        return $error !== null ? $redirect->withErrors([$error]) : $redirect->with('success', $success);
    }
}
