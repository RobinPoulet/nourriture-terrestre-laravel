@auth
    <form action="{{ route('logout') }}" method="post" class="flex items-center gap-2 px-4 md:px-0">
        @csrf
        <span class="flex items-center gap-1.5 text-sm font-medium text-gray-700 dark:text-gray-200">
            <i class="bi bi-person-circle text-indigo-500 dark:text-indigo-400"></i>
            {{ auth()->user()->name }}
        </span>
        <button type="submit" title="Se déconnecter"
                class="p-2 rounded-lg text-gray-500 dark:text-gray-400
                       hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-gray-700
                       transition-colors">
            <i class="bi bi-box-arrow-right"></i>
            <span class="sr-only">Se déconnecter</span>
        </button>
    </form>
@else
    <a href="{{ route('login') }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-sm font-medium
              text-indigo-600 dark:text-indigo-400
              hover:bg-indigo-50 dark:hover:bg-gray-700 transition-colors">
        <i class="bi bi-box-arrow-in-right"></i> Connexion
    </a>
@endauth
