<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Nourriture Terrestre</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('assets/IMG/favicon-32x32.png') }}" type="image/x-icon">

    <!-- Dark mode init : doit s'exécuter AVANT tout rendu pour éviter le flash -->
    <script>
        if (localStorage.theme === 'dark' ||
            (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>

    <!-- Tailwind config : darkMode 'class' doit être déclaré avant le CDN -->
    <script>tailwind = { config: { darkMode: 'class' } }</script>
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Flowbite -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet"/>
    <!-- Bootstrap Icons (CSS uniquement) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css"/>
    <!-- Flowbite JS en premier (expose Modal globalement) puis scripts app -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js" defer></script>
    <script src="{{ asset('js/index.js') }}" defer></script>
    @stack('scripts')
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen transition-colors duration-300">
@include('partials.navbar')

@if ($errors->any())
    <div class="max-w-screen-xl mx-auto px-4 mt-4 space-y-2">
        @foreach ($errors->all() as $error)
            <div class="flex items-center gap-2 p-4 text-sm
                        text-red-800 dark:text-red-300
                        border border-red-300 dark:border-red-800
                        rounded-lg bg-red-50 dark:bg-red-900/20" role="alert">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                {{ $error }}
            </div>
        @endforeach
    </div>
@endif

@session('success')
    <div class="max-w-screen-xl mx-auto px-4 mt-4">
        <div class="flex items-center gap-2 p-4 text-sm
                    text-green-800 dark:text-green-300
                    border border-green-300 dark:border-green-800
                    rounded-lg bg-green-50 dark:bg-green-900/20" role="alert">
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
            {{ $value }}
        </div>
    </div>
@endsession

@yield('content')

<input type="hidden" id="complete-url" value="{{ url('/') }}">
</body>
</html>
