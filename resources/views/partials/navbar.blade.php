@php
    $navItems = [
        'Le menu'                => 'home',
        'Commander'              => 'orders.create',
        'Afficher les commandes' => 'orders.index',
        'Voter'                  => 'ranking',
        'Classement'             => 'classement',
    ];
    if (auth()->user()?->is_admin) {
        $navItems['Admin'] = 'admin.index';
    }
@endphp
<header class="sticky top-0 z-50
               bg-white dark:bg-gray-800
               border-b-2 border-indigo-500 dark:border-indigo-500
               shadow-sm dark:shadow-black/40
               transition-colors duration-300">
    <div class="w-full px-4">
        <div class="flex items-center justify-between h-16">

            <!-- ── Brand ── -->
            <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                <div class="w-9 h-9 rounded-xl bg-indigo-600 flex items-center justify-center
                            shadow-md shadow-indigo-500/30 group-hover:scale-105 transition-transform duration-200">
                    <img src="{{ asset('assets/IMG/favicon-32x32.png') }}"
                         class="h-5 w-5 brightness-0 invert" alt="Logo">
                </div>
                <div class="hidden sm:block">
                    <span class="font-bold text-gray-900 dark:text-white text-base leading-tight block">
                        Nourriture Terrestre
                    </span>
                    <span class="text-[10px] font-medium uppercase tracking-widest text-indigo-500 dark:text-indigo-400 leading-none">
                        Le menu de la semaine
                    </span>
                </div>
            </a>

            <!-- ── Nav desktop ── -->
            <nav class="hidden md:flex items-center gap-1">
                @foreach ($navItems as $name => $routeName)
                    <a href="{{ route($routeName) }}"
                       @class([
                           'relative px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                           'bg-indigo-600 text-white shadow-md shadow-indigo-500/30' => request()->routeIs($routeName),
                           'text-gray-700 dark:text-gray-100 hover:text-indigo-600 dark:hover:text-white hover:bg-indigo-50 dark:hover:bg-gray-700' => !request()->routeIs($routeName),
                       ])>
                        {{ $name }}
                    </a>
                @endforeach

                <!-- Séparateur -->
                <div class="w-px h-5 bg-gray-200 dark:bg-gray-600 mx-2"></div>

                @include('partials.theme-toggle')
            </nav>

            <!-- ── Mobile : toggle + hamburger ── -->
            <div class="flex items-center gap-2 md:hidden">
                @include('partials.theme-toggle')
                <button
                    data-collapse-toggle="navbar-mobile"
                    type="button"
                    class="p-2 rounded-lg text-gray-800 dark:text-white
                           hover:bg-gray-100 dark:hover:bg-gray-700
                           focus:outline-none focus:ring-2 focus:ring-indigo-400 transition-colors"
                    aria-controls="navbar-mobile"
                    aria-expanded="false"
                >
                    <span class="sr-only">Ouvrir le menu</span>
                    <i class="bi bi-list text-2xl leading-none"></i>
                </button>
            </div>
        </div>

        <!-- ── Nav mobile (Flowbite collapse) ── -->
        <div id="navbar-mobile" class="hidden md:hidden pb-4 border-t border-gray-100 dark:border-gray-700 mt-1 pt-3">
            <ul class="flex flex-col gap-1">
                @foreach ($navItems as $name => $routeName)
                    <li>
                        <a href="{{ route($routeName) }}"
                           @class([
                               'flex items-center px-4 py-2.5 rounded-lg text-sm font-medium transition-colors',
                               'bg-indigo-600 text-white' => request()->routeIs($routeName),
                               'text-gray-800 dark:text-gray-100 hover:bg-indigo-50 dark:hover:bg-gray-700 hover:text-indigo-600 dark:hover:text-white' => !request()->routeIs($routeName),
                           ])>
                            {{ $name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</header>
