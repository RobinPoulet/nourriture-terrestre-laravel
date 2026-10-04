{{--
    @var \Illuminate\Support\Collection<\App\Models\Dish> $dishes
    @var \Illuminate\Support\Collection<\App\Models\Order> $orders
    @var bool $isOpen
    @var array $tabTotalQuantity
    @var ?array $smsSent
    @var ?int $selectedUserId
    @var bool $canDisplayForm
    @var ?int $formDeadlineTs
--}}
@extends('layouts.app')

@push('scripts')
    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    <script src="{{ asset('js/display-orders.js') }}" defer></script>
@endpush

@php
    $dishColors = [
        ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-600 dark:text-emerald-400'],
        ['bg' => 'bg-amber-100 dark:bg-amber-900/30',     'text' => 'text-amber-600 dark:text-amber-400'],
        ['bg' => 'bg-rose-100 dark:bg-rose-900/30',       'text' => 'text-rose-600 dark:text-rose-400'],
        ['bg' => 'bg-purple-100 dark:bg-purple-900/30',   'text' => 'text-purple-600 dark:text-purple-400'],
        ['bg' => 'bg-cyan-100 dark:bg-cyan-900/30',       'text' => 'text-cyan-600 dark:text-cyan-400'],
    ];
@endphp

@section('content')
<div class="max-w-screen-xl mx-auto px-4 py-8" id="orders-container"
     data-pusher-key="{{ config('services.pusher.key') }}"
     data-pusher-cluster="{{ config('services.pusher.cluster') }}">

    @if ($isOpen)
        @if ($orders->isNotEmpty())

            <!-- Bannière SMS -->
            @if ($smsSent)
                <div id="sms-summary-target" class="mb-5">
                    <div @class([
                        'flex items-start gap-3 px-4 py-3.5 rounded-xl border text-sm',
                        'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300' => $smsSent['status'] === 'success',
                        'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-300' => $smsSent['status'] !== 'success',
                    ])>
                        <i class="bi bi-chat-dots-fill flex-shrink-0 mt-0.5"></i>
                        <div>
                            <p class="font-semibold">SMS de commande</p>
                            <p class="opacity-80 mt-0.5">{{ $smsSent['message'] }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Countdown fermeture formulaire -->
            @if ($canDisplayForm && $formDeadlineTs !== null)
                <div id="form-deadline-banner" class="mb-6 rounded-xl overflow-hidden shadow-md">
                    <div class="bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-4
                                flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 text-white">
                            <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center flex-shrink-0">
                                <i class="bi bi-alarm text-lg"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-sm">Fermeture des commandes</p>
                                <p class="text-white/70 text-xs mt-0.5">Aujourd'hui à 11h15</p>
                            </div>
                        </div>
                        <div id="form-countdown"
                             data-deadline="{{ $formDeadlineTs }}"
                             class="text-3xl font-bold text-white tabular-nums tracking-wider font-mono">
                            --:--:--
                        </div>
                    </div>
                </div>
            @endif

            <!-- Titre section récapitulatif -->
            <h2 class="text-base font-semibold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                <i class="bi bi-bar-chart-fill text-indigo-500"></i>
                Récapitulatif de la commande
            </h2>

            <!-- Stats : un total par plat, puis total commandes -->
            <div class="flex flex-col gap-3 mb-6">

                @foreach ($dishes as $i => $dish)
                    @php $dishTotal = $tabTotalQuantity[$dish->id] ?? 0; @endphp
                    @continue($dishTotal === 0)
                    @php $color = $dishColors[$i % count($dishColors)]; @endphp
                    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:shadow-gray-900/30
                                px-5 py-4 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl {{ $color['bg'] }}
                                    flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-egg-fried {{ $color['text'] }} text-base"></i>
                        </div>
                        <span class="flex-1 text-sm font-medium text-gray-700 dark:text-gray-200 truncate"
                              title="{{ $dish->name }}">
                            {{ $dish->name }}
                        </span>
                        <span class="text-2xl font-bold text-gray-800 dark:text-white tabular-nums">
                            {{ $dishTotal }}
                        </span>
                    </div>
                @endforeach

                <!-- Total commandes (en dernier) -->
                <div class="bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800
                            rounded-xl px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-900/50
                                flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-people-fill text-indigo-600 dark:text-indigo-400 text-base"></i>
                    </div>
                    <span class="flex-1 text-sm font-medium text-indigo-700 dark:text-indigo-300">
                        Commandes au total
                    </span>
                    <span class="text-2xl font-bold text-indigo-700 dark:text-indigo-300 tabular-nums">
                        {{ $orders->count() }}
                    </span>
                </div>

            </div>

            <!-- Titre section détail -->
            <h2 class="text-base font-semibold text-gray-700 dark:text-gray-200 mb-3 flex items-center gap-2">
                <i class="bi bi-table text-indigo-500"></i>
                Détail de la commande
            </h2>

            <!-- Tableau -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:shadow-gray-900/30 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-gray-600 dark:text-gray-300">

                        <thead>
                            <tr class="bg-indigo-600 dark:bg-indigo-800 text-white text-xs uppercase tracking-wide">
                                <th class="px-5 py-3.5 text-left font-semibold whitespace-nowrap">Nom</th>
                                @foreach ($dishes as $dish)
                                    <th class="px-4 py-3.5 text-center font-semibold whitespace-nowrap"
                                        title="{{ $dish->name }}">
                                        {{ $dish->shortName() }}
                                    </th>
                                @endforeach
                                <th class="px-4 py-3.5 text-left font-semibold">Notes</th>
                                <th class="px-4 py-3.5 text-center font-semibold w-24">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">

                            @foreach ($orders as $order)
                                @php
                                    $user = $order->user;
                                    $isMyOrder = $selectedUserId !== null && (int) $order->user_id === (int) $selectedUserId;
                                    $quantities = $order->quantitiesByDish();
                                    $dishesJson = collect($quantities)
                                        ->map(fn ($quantity, $dishId) => ['id' => $dishId, 'quantity' => $quantity])
                                        ->values()
                                        ->toJson();
                                @endphp
                                <tr @class([
                                    'transition-colors',
                                    'bg-indigo-50/50 dark:bg-indigo-900/10 hover:bg-indigo-50 dark:hover:bg-indigo-900/20' => $isMyOrder,
                                    'hover:bg-gray-50 dark:hover:bg-gray-700/50' => !$isMyOrder,
                                ])>

                                    <!-- Nom -->
                                    <td @class([
                                        'px-5 py-3.5 font-semibold text-gray-800 dark:text-gray-100',
                                        'border-l-[3px] border-indigo-500' => $isMyOrder,
                                    ])>
                                        <div class="flex items-center gap-2">
                                            {{ $user?->name }}
                                            @if ($isMyOrder)
                                                <span class="text-[10px] font-bold uppercase tracking-wider
                                                             px-1.5 py-0.5 rounded
                                                             bg-indigo-100 text-indigo-600
                                                             dark:bg-indigo-900/40 dark:text-indigo-300">
                                                    Moi
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Quantités par plat (alignées sur les colonnes du menu) -->
                                    @foreach ($dishes as $dish)
                                        @php $qty = $quantities[$dish->id] ?? 0; @endphp
                                        <td class="px-4 py-3.5 text-center">
                                            @if ($qty === 0)
                                                <span class="text-gray-300 dark:text-gray-600">–</span>
                                            @else
                                                <span class="inline-flex items-center justify-center
                                                             min-w-[1.5rem] h-6 px-2 rounded-full
                                                             text-xs font-bold
                                                             bg-indigo-100 text-indigo-700
                                                             dark:bg-indigo-900/40 dark:text-indigo-300">
                                                    {{ $qty }}
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach

                                    <!-- Notes -->
                                    <td class="px-4 py-3.5 text-gray-500 dark:text-gray-400 max-w-[200px]"
                                        title="{{ $order->perso }}">
                                        <span class="block truncate text-xs">
                                            @if ($order->perso)
                                                {{ $order->perso }}
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">–</span>
                                            @endif
                                        </span>
                                    </td>

                                    <!-- Actions -->
                                    <td class="px-4 py-3.5">
                                        @if ($isMyOrder)
                                            <div class="flex items-center justify-center gap-1.5">
                                                <button data-action="edit-order"
                                                        data-perso="{{ $order->perso }}"
                                                        data-username="{{ $user?->name }}"
                                                        data-order-id="{{ $order->id }}"
                                                        data-order-dishes="{{ $dishesJson }}"
                                                        class="p-1.5 rounded-lg border transition-colors
                                                               text-amber-600 dark:text-amber-400
                                                               border-amber-200 dark:border-amber-800
                                                               hover:bg-amber-50 dark:hover:bg-amber-900/20">
                                                    <i class="bi bi-pencil-square"></i>
                                                </button>
                                                <button data-action="delete-order"
                                                        data-order-id="{{ $order->id }}"
                                                        class="p-1.5 rounded-lg border transition-colors
                                                               text-red-600 dark:text-red-400
                                                               border-red-200 dark:border-red-800
                                                               hover:bg-red-50 dark:hover:bg-red-900/20">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            </div>
                                        @else
                                            <span class="block text-center text-gray-300 dark:text-gray-600">–</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach

                        </tbody>
                    </table>
                </div>
            </div>

        @else
            <div class="flex flex-col items-center gap-3 py-16 text-center">
                <div class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-2xl
                            flex items-center justify-center">
                    <i class="bi bi-inbox text-gray-400 text-2xl"></i>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pas de commande aujourd'hui</p>
            </div>
        @endif

    @else
        <div class="flex flex-col items-center gap-3 py-16 text-center">
            <div class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-2xl
                        flex items-center justify-center">
                <i class="bi bi-lock text-gray-400 text-2xl"></i>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Nourriture terrestre est fermé cette semaine
            </p>
        </div>
    @endif

    @include('orders.partials.edit-modal')

</div>
@endsection
