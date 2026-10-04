{{--
    @var \Illuminate\Support\Collection<\App\Models\User> $users
    @var \Illuminate\Support\Collection<\App\Models\Announcement> $announcements
    @var array<string, string> $settings
--}}
@extends('layouts.app')

@push('scripts')
    <script src="{{ asset('js/admin.js') }}" defer></script>
@endpush

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">

    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Administration</h1>
        <form action="{{ route('admin.logout') }}" method="post">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 text-sm font-medium
                           text-gray-600 dark:text-gray-300
                           border border-gray-300 dark:border-gray-600
                           rounded-lg px-4 py-2
                           hover:bg-gray-100 dark:hover:bg-gray-700
                           transition-colors">
                <i class="bi bi-box-arrow-right"></i> Se déconnecter
            </button>
        </form>
    </div>

    <div class="flex gap-6 items-start">

        <!-- ── Sidebar gauche ────────────────────────────────────── -->
        <aside class="w-52 flex-shrink-0">
            <nav class="flex flex-col gap-1
                        bg-white dark:bg-gray-800
                        border border-gray-200 dark:border-gray-700
                        rounded-xl p-2">
                @foreach (['users' => ['bi-people', 'Utilisateurs'], 'announcements' => ['bi-megaphone', 'Annonces'], 'settings' => ['bi-gear', 'Paramètres']] as $tab => [$icon, $label])
                    <button data-tab="{{ $tab }}"
                            class="admin-tab w-full text-left px-4 py-2.5 rounded-lg text-sm font-medium
                                   transition-colors inline-flex items-center gap-2.5
                                   text-gray-600 dark:text-gray-300
                                   hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-900 dark:hover:text-white">
                        <i class="bi {{ $icon }} text-base"></i> {{ $label }}
                    </button>
                @endforeach
            </nav>
        </aside>

        <!-- ── Contenu principal ─────────────────────────────────── -->
        <div class="flex-1 min-w-0">

    <!-- ══════════════════════════════════════════════════════════════ -->
    <!-- Panneau utilisateurs                                           -->
    <!-- ══════════════════════════════════════════════════════════════ -->
    <section id="panel-users" class="admin-panel">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Utilisateurs</h2>
            <button id="btn-add-user"
                    class="inline-flex items-center gap-2 text-sm font-medium
                           text-indigo-600 dark:text-indigo-400
                           border border-indigo-400 dark:border-indigo-700
                           rounded-lg px-4 py-2
                           hover:bg-indigo-50 dark:hover:bg-indigo-900/20
                           transition-colors">
                <i class="bi bi-person-plus"></i> Ajouter un utilisateur
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:shadow-gray-900/30 overflow-hidden">
            <table class="w-full text-sm text-gray-700 dark:text-gray-300">
                <thead class="text-xs text-white uppercase bg-indigo-600 dark:bg-indigo-800">
                    <tr>
                        <th class="px-6 py-3 text-left">Nom</th>
                        <th class="px-6 py-3 text-left">Rôle</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-3 font-medium text-gray-800 dark:text-gray-100">
                                {{ $user->name }}
                            </td>
                            <td class="px-6 py-3">
                                @if ($user->is_admin)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold
                                                 px-2 py-0.5 rounded-full
                                                 bg-indigo-100 text-indigo-700
                                                 dark:bg-indigo-900/40 dark:text-indigo-300">
                                        <i class="bi bi-shield-fill"></i> Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold
                                                 px-2 py-0.5 rounded-full
                                                 bg-gray-100 text-gray-500
                                                 dark:bg-gray-700 dark:text-gray-400">
                                        <i class="bi bi-person"></i> Utilisateur
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex justify-end items-center gap-2">
                                    <!-- Toggle rôle -->
                                    <form action="{{ route('admin.users.role', $user) }}" method="post" class="inline-flex">
                                        @csrf
                                        <button type="submit"
                                                title="{{ $user->is_admin ? 'Rétrograder' : 'Promouvoir admin' }}"
                                                @class([
                                                    'p-1.5 rounded-lg border transition-colors',
                                                    'text-indigo-600 dark:text-indigo-400 border-indigo-300 dark:border-indigo-700 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' => $user->is_admin,
                                                    'text-gray-400 dark:text-gray-500 border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50' => !$user->is_admin,
                                                ])>
                                            <i class="bi bi-shield{{ $user->is_admin ? '-fill' : '' }}"></i>
                                        </button>
                                    </form>
                                    <!-- Réinitialiser l'appareil -->
                                    @if ($user->cookie_hash !== null)
                                        <button type="button"
                                                title="Réinitialiser l'appareil associé"
                                                class="p-1.5
                                                       text-sky-600 dark:text-sky-400
                                                       hover:bg-sky-50 dark:hover:bg-sky-900/20
                                                       rounded-lg border border-sky-300 dark:border-sky-700
                                                       transition-colors"
                                                onclick="confirmResetDevice({{ $user->id }})">
                                            <i class="bi bi-phone"></i>
                                        </button>
                                    @endif
                                    <!-- Éditer -->
                                    <button class="btn-edit p-1.5
                                                   text-amber-600 dark:text-amber-400
                                                   hover:bg-amber-50 dark:hover:bg-amber-900/20
                                                   rounded-lg border border-amber-300 dark:border-amber-700
                                                   transition-colors"
                                            data-user-name="{{ $user->name }}"
                                            data-user-id="{{ $user->id }}">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <!-- Supprimer -->
                                    <button class="p-1.5
                                                   text-red-600 dark:text-red-400
                                                   hover:bg-red-50 dark:hover:bg-red-900/20
                                                   rounded-lg border border-red-300 dark:border-red-700
                                                   transition-colors"
                                            onclick="confirmDeleteUser({{ $user->id }})">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════ -->
    <!-- Panneau annonces ticker                                        -->
    <!-- ══════════════════════════════════════════════════════════════ -->
    <section id="panel-announcements" class="admin-panel hidden">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Annonces (ticker)</h2>
            <button id="btn-add-announcement"
                    class="inline-flex items-center gap-2 text-sm font-medium
                           text-indigo-600 dark:text-indigo-400
                           border border-indigo-400 dark:border-indigo-700
                           rounded-lg px-4 py-2
                           hover:bg-indigo-50 dark:hover:bg-indigo-900/20
                           transition-colors">
                <i class="bi bi-megaphone"></i> Ajouter une annonce
            </button>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:shadow-gray-900/30 overflow-hidden">
            @if ($announcements->isEmpty())
                <p class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">Aucune annonce pour le moment.</p>
            @else
                <table class="w-full text-sm text-gray-700 dark:text-gray-300">
                    <thead class="text-xs text-white uppercase bg-indigo-600 dark:bg-indigo-800">
                        <tr>
                            <th class="px-6 py-3 text-left">Message</th>
                            <th class="px-6 py-3 text-left">Visibilité</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($announcements as $announcement)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <td class="px-6 py-3 text-gray-800 dark:text-gray-100 max-w-xs truncate">
                                    {{ $announcement->message }}
                                </td>
                                <td class="px-6 py-3">
                                    @if ($announcement->is_visible)
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold
                                                     px-2 py-0.5 rounded-full
                                                     bg-emerald-100 text-emerald-700
                                                     dark:bg-emerald-900/40 dark:text-emerald-300">
                                            <i class="bi bi-eye-fill"></i> Visible
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold
                                                     px-2 py-0.5 rounded-full
                                                     bg-gray-100 text-gray-500
                                                     dark:bg-gray-700 dark:text-gray-400">
                                            <i class="bi bi-eye-slash"></i> Masquée
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    <div class="flex justify-end items-center gap-2">
                                        <!-- Toggle visibilité -->
                                        <form action="{{ route('admin.announcements.toggle', $announcement) }}" method="post" class="inline-flex">
                                            @csrf
                                            <button type="submit"
                                                    title="{{ $announcement->is_visible ? 'Masquer' : 'Afficher' }}"
                                                    @class([
                                                        'p-1.5 rounded-lg border transition-colors',
                                                        'text-emerald-600 dark:text-emerald-400 border-emerald-300 dark:border-emerald-700 hover:bg-emerald-50 dark:hover:bg-emerald-900/20' => $announcement->is_visible,
                                                        'text-gray-400 dark:text-gray-500 border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50' => !$announcement->is_visible,
                                                    ])>
                                                <i class="bi bi-eye{{ $announcement->is_visible ? '-fill' : '-slash' }}"></i>
                                            </button>
                                        </form>
                                        <!-- Éditer -->
                                        <button class="btn-edit-announcement p-1.5
                                                       text-amber-600 dark:text-amber-400
                                                       hover:bg-amber-50 dark:hover:bg-amber-900/20
                                                       rounded-lg border border-amber-300 dark:border-amber-700
                                                       transition-colors"
                                                data-announcement-id="{{ $announcement->id }}"
                                                data-announcement-message="{{ $announcement->message }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <!-- Supprimer -->
                                        <button class="p-1.5
                                                       text-red-600 dark:text-red-400
                                                       hover:bg-red-50 dark:hover:bg-red-900/20
                                                       rounded-lg border border-red-300 dark:border-red-700
                                                       transition-colors"
                                                onclick="confirmDeleteAnnouncement({{ $announcement->id }})">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </section>

    <!-- ══════════════════════════════════════════════════════════════ -->
    <!-- Panneau paramètres                                             -->
    <!-- ══════════════════════════════════════════════════════════════ -->
    <section id="panel-settings" class="admin-panel hidden">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white">Paramètres</h2>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:shadow-gray-900/30 p-6">
            <form action="{{ route('admin.settings.update') }}" method="post" class="space-y-6">
                @csrf

                <!-- Bande d'annonces -->
                <div class="flex items-start gap-4">
                    <div class="flex items-center h-5 mt-0.5">
                        <input id="ticker_disabled" name="ticker_disabled" type="checkbox" value="1"
                               @checked($settings['ticker_disabled'] === '1')
                               class="w-4 h-4 text-indigo-600 bg-gray-100 dark:bg-gray-700
                                      border-gray-300 dark:border-gray-600
                                      rounded focus:ring-indigo-500 dark:focus:ring-indigo-600">
                    </div>
                    <div>
                        <label for="ticker_disabled" class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            Désactiver la bande d'annonces
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Masque le ticker sur la page d'accueil (masqué aussi si aucune annonce n'est visible).
                        </p>
                    </div>
                </div>

                <div class="border-t border-gray-100 dark:border-gray-700"></div>

                @include('admin.partials.toggle-setting', [
                    'key' => 'force_open_form',
                    'label' => 'Ouvrir le formulaire de commande manuellement',
                    'help' => "Force l'affichage du formulaire de commande en dehors du créneau habituel (lundi avant 11h15).",
                ])

                <div class="border-t border-gray-100 dark:border-gray-700"></div>

                @include('admin.partials.toggle-setting', [
                    'key' => 'force_open_vote',
                    'label' => 'Ouvrir le vote manuellement',
                    'help' => "Force l'ouverture du vote sur les plats en dehors du créneau habituel (lundi 12h45 – samedi).",
                ])

                <div class="border-t border-gray-100 dark:border-gray-700"></div>

                <!-- Heure d'envoi du SMS -->
                <div class="flex items-start gap-4">
                    <div class="flex items-center h-5 mt-0.5 pt-0.5">
                        <i class="bi bi-clock text-indigo-500 dark:text-indigo-400 text-base"></i>
                    </div>
                    <div class="flex-1">
                        <label for="sms_send_time" class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            Heure d'envoi du SMS
                        </label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 mb-2">
                            Heure à laquelle le récapitulatif des commandes est envoyé automatiquement.
                        </p>
                        <input id="sms_send_time" name="sms_send_time" type="time"
                               value="{{ $settings['sms_send_time'] }}"
                               class="bg-gray-50 dark:bg-gray-700
                                      border border-gray-300 dark:border-gray-600
                                      text-gray-900 dark:text-gray-100
                                      text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500
                                      p-2.5 w-36">
                    </div>
                </div>

                <div class="flex justify-end pt-2 border-t border-gray-100 dark:border-gray-700">
                    <button type="submit"
                            class="text-sm font-medium text-white bg-indigo-600 rounded-lg px-4 py-2
                                   hover:bg-indigo-700 transition-colors shadow-md shadow-indigo-500/20">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </section>

        </div><!-- fin contenu principal -->
    </div><!-- fin flex -->
</div><!-- fin max-w -->

@include('admin.partials.user-modal')
@include('admin.partials.announcement-modal')
@endsection
