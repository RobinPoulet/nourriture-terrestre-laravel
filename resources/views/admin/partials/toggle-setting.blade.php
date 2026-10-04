{{-- Case à cocher d'un paramètre "forcer l'ouverture", avec badge "Actif" --}}
@php $enabled = $settings[$key] === '1'; @endphp
<div class="flex items-start gap-4">
    <div class="flex items-center h-5 mt-0.5">
        <input id="{{ $key }}" name="{{ $key }}" type="checkbox" value="1" @checked($enabled)
               class="w-4 h-4 text-emerald-600 bg-gray-100 dark:bg-gray-700
                      border-gray-300 dark:border-gray-600
                      rounded focus:ring-emerald-500 dark:focus:ring-emerald-600">
    </div>
    <div>
        <label for="{{ $key }}" class="text-sm font-medium text-gray-800 dark:text-gray-200 flex items-center gap-2">
            {{ $label }}
            @if ($enabled)
                <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider
                             px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700
                             dark:bg-emerald-900/40 dark:text-emerald-300">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Actif
                </span>
            @endif
        </label>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
            {{ $help }}
        </p>
    </div>
</div>
