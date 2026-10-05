@extends('layouts.app')

@section('content')
<x-auth-card title="Mot de passe oublié" icon="bi-key">
    <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">
        Indique ton email : tu recevras un lien pour choisir un nouveau mot de passe.
    </p>

    <form action="{{ route('password.email') }}" method="post" class="space-y-4">
        @csrf

        <x-auth-input name="email" type="email" label="Email" :value="old('email')" autocomplete="username" autofocus />

        <button type="submit"
                class="w-full inline-flex justify-center items-center gap-2
                       text-sm font-medium text-white
                       bg-indigo-600 hover:bg-indigo-700
                       rounded-lg px-4 py-2.5 transition-colors">
            <i class="bi bi-envelope"></i> Envoyer le lien
        </button>
    </form>

    <a href="{{ route('login') }}" class="mt-6 inline-block text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
        <i class="bi bi-arrow-left"></i> Retour à la connexion
    </a>
</x-auth-card>
@endsection
