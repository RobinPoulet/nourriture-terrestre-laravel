@props(['category' => null])
@if ($category !== null)
    @php
        $config = match ($category) {
            'entree'  => ['icon' => 'bi-leaf',    'label' => 'Entrée',  'css' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'],
            'dessert' => ['icon' => 'bi-cup-hot', 'label' => 'Dessert', 'css' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
            default   => ['icon' => 'bi-fire',    'label' => 'Plat',    'css' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300'],
        };
    @endphp
    <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-[10px] font-semibold flex-shrink-0 {{ $config['css'] }}"><i class="bi {{ $config['icon'] }} text-[9px]"></i>{{ $config['label'] }}</span>
@endif
