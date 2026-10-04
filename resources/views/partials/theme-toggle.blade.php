<button
    onclick="toggleTheme()"
    aria-label="Changer de thème"
    class="relative flex items-center justify-center w-9 h-9 rounded-lg transition-all duration-200
           text-gray-600 dark:text-gray-100
           hover:bg-gray-100 dark:hover:bg-gray-700
           hover:text-indigo-600 dark:hover:text-amber-400
           focus:outline-none focus:ring-2 focus:ring-indigo-400"
>
    <!-- Icône lune (mode clair → cliquer pour passer en sombre) -->
    <i class="bi bi-moon-stars text-base dark:hidden"></i>
    <!-- Icône soleil (mode sombre → cliquer pour passer en clair) -->
    <i class="bi bi-sun text-xl hidden dark:inline text-amber-400"></i>
</button>
