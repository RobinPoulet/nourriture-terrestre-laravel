@extends('layouts.app')

@section('content')
<div class="max-w-sm mx-auto px-4 py-16">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:shadow-gray-900/30 p-6">
        <h1 class="flex items-center gap-2 text-xl font-bold text-gray-800 dark:text-white mb-6">
            <i class="bi bi-shield-lock"></i> Connexion admin
        </h1>

        <form action="{{ route('admin.authenticate') }}" method="post" class="space-y-4">
            @csrf

            <div>
                <label for="admin-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Nom
                </label>
                <input type="text" id="admin-name" name="name" required autocomplete="username"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                              bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white text-sm
                              px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
            </div>

            <div>
                <label for="admin-password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Mot de passe
                </label>
                <input type="password" id="admin-password" name="password" required autocomplete="current-password"
                       class="w-full rounded-lg border border-gray-300 dark:border-gray-600
                              bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white text-sm
                              px-3 py-2 focus:ring-2 focus:ring-indigo-400 focus:border-indigo-400">
            </div>

            <button type="submit"
                    class="w-full inline-flex justify-center items-center gap-2
                           text-sm font-medium text-white
                           bg-indigo-600 hover:bg-indigo-700
                           rounded-lg px-4 py-2.5 transition-colors">
                <i class="bi bi-box-arrow-in-right"></i> Se connecter
            </button>
        </form>
    </div>
</div>
@endsection
