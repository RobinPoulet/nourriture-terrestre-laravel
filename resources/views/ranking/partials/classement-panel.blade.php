{{-- @var array<int, array{dish_id: int, dish_name: string, category: ?string, avg: float, count: int}> $items --}}
@if (empty($items))
    <div class="flex flex-col items-center gap-3 py-16 text-center">
        <div class="w-12 h-12 bg-gray-100 dark:bg-gray-800 rounded-xl flex items-center justify-center">
            <i class="bi bi-star text-gray-400 text-xl"></i>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400">Aucun plat dans cette catégorie.</p>
    </div>
@else
    @php
        $top  = array_slice($items, 0, 3);
        $rest = array_slice($items, count($top));
        $n    = count($top);

        $podiumCfg = [
            0 => [
                'icon'      => 'bi-trophy-fill',
                'iconColor' => 'text-amber-400',
                'border'    => 'border-2 border-amber-300 dark:border-amber-600',
                'bg'        => 'from-amber-50 to-white dark:from-amber-900/20 dark:to-gray-800',
                'shadow'    => 'shadow-md shadow-amber-200/50 dark:shadow-amber-900/30',
                'mt'        => '',
                'pad'       => 'px-4 py-5',
                'iconSize'  => 'text-3xl',
                'nameClass' => 'text-sm font-bold',
            ],
            1 => [
                'icon'      => 'bi-award-fill',
                'iconColor' => 'text-slate-400',
                'border'    => 'border border-gray-200 dark:border-gray-700',
                'bg'        => 'from-gray-50 to-white dark:from-gray-800 dark:to-gray-800/80',
                'shadow'    => 'shadow-sm',
                'mt'        => 'mt-6',
                'pad'       => 'px-3 py-4',
                'iconSize'  => 'text-2xl',
                'nameClass' => 'text-xs font-semibold',
            ],
            2 => [
                'icon'      => 'bi-award-fill',
                'iconColor' => 'text-amber-700 dark:text-amber-500',
                'border'    => 'border border-orange-200 dark:border-orange-900',
                'bg'        => 'from-orange-50/60 to-white dark:from-orange-900/10 dark:to-gray-800',
                'shadow'    => 'shadow-sm',
                'mt'        => 'mt-10',
                'pad'       => 'px-3 py-4',
                'iconSize'  => 'text-xl',
                'nameClass' => 'text-xs font-semibold',
            ],
        ];

        // 2e à gauche, 1er au centre, 3e à droite
        $displayOrder = match ($n) {
            1       => [0 => $top[0]],
            2       => [1 => $top[1], 0 => $top[0]],
            default => [1 => $top[1], 0 => $top[0], 2 => $top[2]],
        };
    @endphp

    <div @class(['flex items-end gap-3 mb-6', 'max-w-xs mx-auto' => $n === 1])>
        @foreach ($displayOrder as $idx => $entry)
            @php $c = $podiumCfg[$idx]; @endphp
            <div class="flex-1 {{ $c['mt'] }}">
                <div class="rounded-2xl {{ $c['border'] }} bg-gradient-to-b {{ $c['bg'] }}
                            {{ $c['pad'] }} {{ $c['shadow'] }}
                            flex flex-col items-center gap-2 text-center overflow-hidden">
                    <i class="bi {{ $c['icon'] }} {{ $c['iconColor'] }} {{ $c['iconSize'] }}"></i>
                    <p class="{{ $c['nameClass'] }} text-gray-900 dark:text-white leading-tight w-full"
                       title="{{ $entry['dish_name'] }}">
                        {{ $entry['dish_name'] }}
                    </p>
                    <x-category-badge :category="$entry['category']" />
                    <x-stars :avg="$entry['avg']" />
                    <span class="text-xs text-gray-500 dark:text-gray-400 tabular-nums">
                        {{ number_format($entry['avg'], 1) }} &middot; {{ $entry['count'] }} vote{{ $entry['count'] > 1 ? 's' : '' }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    @if (!empty($rest))
        <div class="flex flex-col gap-2">
            @foreach ($rest as $i => $entry)
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:shadow-gray-900/30
                            px-5 py-4 flex items-center gap-4">
                    <div class="w-8 text-center flex-shrink-0">
                        <span class="text-sm font-semibold text-gray-400 dark:text-gray-500">{{ $i + $n + 1 }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <x-category-badge :category="$entry['category']" />
                            <p class="text-sm font-semibold text-gray-800 dark:text-gray-100 truncate flex-1"
                               title="{{ $entry['dish_name'] }}">
                                {{ $entry['dish_name'] }}
                            </p>
                        </div>
                        <div class="mt-1.5 h-1.5 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                            <div class="h-full rounded-full bg-amber-400 transition-all duration-500"
                                 style="width: {{ round(($entry['avg'] / 5) * 100) }}%"></div>
                        </div>
                    </div>
                    <div class="flex flex-col items-end gap-0.5 flex-shrink-0 text-right">
                        <x-stars :avg="$entry['avg']" />
                        <span class="text-xs text-gray-500 dark:text-gray-400 tabular-nums">
                            {{ number_format($entry['avg'], 1) }} &middot; {{ $entry['count'] }} vote{{ $entry['count'] > 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endif
