{{--
    @var array<int, array{dish_id: int, dish_name: string, category: ?string, avg: float, count: int}> $ranking
--}}
@extends('layouts.app')

@php
    $panels = [
        'tous'    => $ranking,
        'entree'  => array_values(array_filter($ranking, fn ($entry) => $entry['category'] === 'entree')),
        'plat'    => array_values(array_filter($ranking, fn ($entry) => $entry['category'] === 'plat')),
        'dessert' => array_values(array_filter($ranking, fn ($entry) => $entry['category'] === 'dessert')),
    ];

    $tabConfig = [
        'tous'    => ['label' => 'Tous',    'icon' => 'bi-list-ul'],
        'entree'  => ['label' => 'Entrée',  'icon' => 'bi-leaf'],
        'plat'    => ['label' => 'Plat',    'icon' => 'bi-fire'],
        'dessert' => ['label' => 'Dessert', 'icon' => 'bi-cup-hot'],
    ];
@endphp

@section('content')
<div class="max-w-3xl mx-auto px-4 py-10">

    <!-- En-tête -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i class="bi bi-trophy text-amber-500"></i>
            Classement général
        </h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Moyenne des notes sur l'ensemble des menus
        </p>
    </div>

    @if (empty($ranking))
        <div class="flex flex-col items-center gap-3 py-20 text-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center">
                <i class="bi bi-star text-gray-400 text-2xl"></i>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Aucun plat n'a encore été noté.</p>
        </div>
    @else

        <!-- Onglets de filtre -->
        <div class="flex gap-1 mb-6 p-1 bg-gray-100 dark:bg-gray-800/60 rounded-xl">
            @foreach ($tabConfig as $key => $tab)
                @php $isActive = $key === 'tous'; @endphp
                <button data-classement-tab="{{ $key }}"
                        @class([
                            'classement-tab-btn flex-1 flex items-center justify-center gap-1 sm:gap-1.5 px-2 py-2 rounded-lg text-xs font-medium transition-all duration-200',
                            'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow' => $isActive,
                            'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' => !$isActive,
                        ])>
                    <i class="bi {{ $tab['icon'] }}"></i>
                    <span class="hidden sm:inline">{{ $tab['label'] }}</span>
                    <span @class([
                        'tab-count tabular-nums text-[10px]',
                        'text-indigo-500 dark:text-indigo-400' => $isActive,
                        'text-gray-400 dark:text-gray-500' => !$isActive,
                    ])>
                        {{ count($panels[$key]) }}
                    </span>
                </button>
            @endforeach
        </div>

        <!-- Panels -->
        @foreach ($panels as $key => $items)
            <div id="classement-panel-{{ $key }}" @class(['classement-panel', 'hidden' => $key !== 'tous'])>
                @include('ranking.partials.classement-panel', ['items' => $items])
            </div>
        @endforeach

    @endif

</div>

<script>
(function () {
    const tabs   = document.querySelectorAll('[data-classement-tab]');
    const panels = document.querySelectorAll('.classement-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = tab.dataset.classementTab;

            tabs.forEach(t => {
                const active = t.dataset.classementTab === target;
                t.classList.toggle('bg-white',              active);
                t.classList.toggle('dark:bg-gray-700',      active);
                t.classList.toggle('text-gray-900',         active);
                t.classList.toggle('dark:text-white',       active);
                t.classList.toggle('shadow',                active);
                t.classList.toggle('text-gray-500',         !active);
                t.classList.toggle('dark:text-gray-400',    !active);
                t.classList.toggle('hover:text-gray-700',   !active);
                t.classList.toggle('dark:hover:text-gray-300', !active);

                const badge = t.querySelector('.tab-count');
                if (badge) {
                    badge.classList.toggle('text-indigo-500',       active);
                    badge.classList.toggle('dark:text-indigo-400',  active);
                    badge.classList.toggle('text-gray-400',         !active);
                    badge.classList.toggle('dark:text-gray-500',    !active);
                }
            });

            panels.forEach(p => {
                p.classList.toggle('hidden', p.id !== 'classement-panel-' + target);
            });
        });
    });
})();
</script>
@endsection
