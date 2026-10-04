@props(['title', 'icon'])
<div class="max-w-sm mx-auto px-4 py-16">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:shadow-gray-900/30 p-6">
        <h1 class="flex items-center gap-2 text-xl font-bold text-gray-800 dark:text-white mb-6">
            <i class="bi {{ $icon }}"></i> {{ $title }}
        </h1>

        {{ $slot }}
    </div>
</div>
