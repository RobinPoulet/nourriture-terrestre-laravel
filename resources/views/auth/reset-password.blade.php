{{--
    @var string $token
    @var string $email
--}}
@extends('layouts.app')

@section('content')
<x-auth-card title="Choisis ton mot de passe" icon="bi-shield-lock">
    <form action="{{ route('password.store') }}" method="post" class="space-y-4">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <x-auth-input name="email" type="email" label="Email" :value="old('email', $email)" autocomplete="username" />
        <x-auth-input name="password" type="password" label="Mot de passe (8 caractères minimum)" autocomplete="new-password" autofocus />
        <x-auth-input name="password_confirmation" type="password" label="Confirmation" autocomplete="new-password" />

        <button type="submit"
                class="w-full inline-flex justify-center items-center gap-2
                       text-sm font-medium text-white
                       bg-indigo-600 hover:bg-indigo-700
                       rounded-lg px-4 py-2.5 transition-colors">
            <i class="bi bi-check-lg"></i> Enregistrer et me connecter
        </button>
    </form>
</x-auth-card>
@endsection
