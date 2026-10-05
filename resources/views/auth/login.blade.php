@extends('layouts.app')

@section('content')
<x-auth-card title="Connexion" icon="bi-person-circle">
    <form action="{{ route('login.store') }}" method="post" class="space-y-4">
        @csrf

        <x-auth-input name="email" type="email" label="Email" :value="old('email')" autocomplete="username" autofocus />
        <x-auth-input name="password" type="password" label="Mot de passe" autocomplete="current-password" />

        <div class="flex items-center justify-between">
            <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="remember" value="1" checked
                       class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-400">
                Rester connecté
            </label>
            <a href="{{ route('password.request') }}"
               class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                Mot de passe oublié ?
            </a>
        </div>

        <button type="submit"
                class="w-full inline-flex justify-center items-center gap-2
                       text-sm font-medium text-white
                       bg-indigo-600 hover:bg-indigo-700
                       rounded-lg px-4 py-2.5 transition-colors">
            <i class="bi bi-box-arrow-in-right"></i> Se connecter
        </button>
    </form>

    <p class="mt-6 text-xs text-gray-500 dark:text-gray-400">
        Pas encore de compte ? Demande une invitation à l'admin : tu recevras un lien pour choisir ton mot de passe.
    </p>
</x-auth-card>
@endsection
