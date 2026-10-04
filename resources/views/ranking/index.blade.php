{{--
    @var \App\Models\Menu $menu
    @var \Illuminate\Support\Collection<\App\Models\Dish> $dishes
    @var array $ranking dish_id => [avg, count]
    @var bool $canVote
    @var ?\App\Models\User $user
    @var int[] $userOrderedDishIds
    @var array $userRatings dish_id => rating
--}}
@extends('layouts.app')

@push('scripts')
    <script src="{{ asset('js/ranking.js') }}" defer></script>
@endpush

@section('content')
<div class="max-w-4xl mx-auto px-4 py-10">

    <!-- ── En-tête ──────────────────────────────────────────────────── -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i class="bi bi-trophy text-amber-500"></i>
                    Classement de la semaine
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Menu du {{ $menu->creation_date }}
                </p>
            </div>

            <!-- Badge statut vote -->
            @if ($canVote)
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium
                             bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300
                             border border-emerald-200 dark:border-emerald-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Vote ouvert
                </span>
            @else
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium
                             bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400
                             border border-gray-200 dark:border-gray-600">
                    <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                    Vote fermé
                </span>
            @endif
        </div>

        <!-- Bandeau contextuel utilisateur -->
        @if (!$user)
            <div class="mt-4 flex items-start gap-2 p-3 text-sm
                        bg-blue-50 dark:bg-blue-900/20
                        border border-blue-200 dark:border-blue-800
                        text-blue-700 dark:text-blue-300 rounded-lg">
                <i class="bi bi-info-circle-fill flex-shrink-0 mt-0.5"></i>
                Passez une commande pour pouvoir voter sur les plats de votre assiette.
            </div>
        @elseif ($canVote && empty($userOrderedDishIds))
            <div class="mt-4 flex items-start gap-2 p-3 text-sm
                        bg-amber-50 dark:bg-amber-900/20
                        border border-amber-200 dark:border-amber-800
                        text-amber-700 dark:text-amber-300 rounded-lg">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-0.5"></i>
                Vous n'avez pas passé de commande cette semaine. Le vote est réservé aux plats commandés.
            </div>
        @elseif (!$canVote)
            <div class="mt-4 flex items-start gap-2 p-3 text-sm
                        bg-gray-50 dark:bg-gray-700/50
                        border border-gray-200 dark:border-gray-600
                        text-gray-600 dark:text-gray-300 rounded-lg">
                <i class="bi bi-clock flex-shrink-0 mt-0.5"></i>
                Le vote ouvre le lundi à 12h45, soit 1h30 après la fermeture des commandes.
                @if (!empty($userRatings))
                    Vos votes de la semaine sont affichés ci-dessous.
                @endif
            </div>
        @endif
    </div>

    <!-- ── Tableau de classement ─────────────────────────────────────── -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm dark:shadow-gray-900/30 overflow-hidden">
        <table class="w-full text-sm text-gray-700 dark:text-gray-300">
            <thead class="text-xs text-white uppercase bg-indigo-600 dark:bg-indigo-800">
                <tr>
                    <th class="px-4 py-3 text-center w-12">#</th>
                    <th class="px-6 py-3 text-left">Plat</th>
                    <th class="px-6 py-3 text-center">Note globale</th>
                    @if ($canVote)
                        <th class="px-6 py-3 text-center">Votre vote</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach ($dishes as $rank => $dish)
                    @php
                        $dishId      = (int) $dish->id;
                        $hasRating   = isset($ranking[$dishId]);
                        $avg         = $hasRating ? $ranking[$dishId]['avg'] : 0.0;
                        $count       = $hasRating ? $ranking[$dishId]['count'] : 0;
                        $canVoteThis = $canVote && in_array($dishId, $userOrderedDishIds, true) && !isset($userRatings[$dishId]);
                        $userNote    = $userRatings[$dishId] ?? 0;
                    @endphp
                    <tr @class([
                        'hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors',
                        'bg-amber-50/40 dark:bg-amber-900/10' => $canVoteThis,
                    ])>

                        <!-- Rang -->
                        <td class="px-4 py-4 text-center">
                            @if ($hasRating && $rank === 0)
                                <span class="text-amber-500 text-lg"><i class="bi bi-trophy-fill"></i></span>
                            @elseif ($hasRating)
                                <span class="text-gray-400 dark:text-gray-500 font-medium">{{ $rank + 1 }}</span>
                            @else
                                <span class="text-gray-300 dark:text-gray-600">–</span>
                            @endif
                        </td>

                        <!-- Nom du plat -->
                        <td class="px-6 py-4 font-medium text-gray-800 dark:text-gray-100">
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <x-category-badge :category="$dish->category" />
                                <span>{{ $dish->name }}</span>
                                @if ($canVoteThis)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-semibold uppercase
                                                 tracking-wider px-1.5 py-0.5 rounded
                                                 bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                        <i class="bi bi-cart-check-fill"></i> Commandé
                                    </span>
                                @endif
                            </div>
                        </td>

                        <!-- Note globale -->
                        <td class="px-6 py-4 text-center">
                            @if ($hasRating)
                                <div class="flex flex-col items-center gap-0.5">
                                    <span id="stars-{{ $dishId }}" class="inline-flex gap-0.5">
                                        <x-stars :avg="$avg" />
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        <span id="avg-{{ $dishId }}">{{ number_format($avg, 1) }}</span>
                                        &middot;
                                        <span id="count-{{ $dishId }}">{{ $count }} vote{{ $count > 1 ? 's' : '' }}</span>
                                    </span>
                                </div>
                            @else
                                <span class="text-gray-400 dark:text-gray-500 text-xs italic">Aucun vote</span>
                            @endif
                        </td>

                        <!-- Votre vote (masqué si vote fermé) -->
                        @if ($canVote)
                            <td class="px-6 py-4 text-center">
                                @if ($canVoteThis)
                                    <div class="star-group inline-flex gap-1 cursor-pointer"
                                         data-dish-id="{{ $dishId }}"
                                         data-current="{{ $userNote }}">
                                        @for ($s = 1; $s <= 5; $s++)
                                            <button @class([
                                                        'star-btn text-xl transition-colors',
                                                        'text-amber-400' => $userNote >= $s,
                                                        'text-gray-300 dark:text-gray-600' => $userNote < $s,
                                                    ])
                                                    data-value="{{ $s }}"
                                                    title="{{ $s }} étoile{{ $s > 1 ? 's' : '' }}">
                                                <i class="bi bi-star{{ $userNote >= $s ? '-fill' : '' }}"></i>
                                            </button>
                                        @endfor
                                    </div>
                                @elseif ($userNote > 0)
                                    <div class="flex flex-col items-center gap-0.5">
                                        <x-stars :avg="(float) $userNote" />
                                        <span class="text-xs text-gray-400 dark:text-gray-500">Votre note</span>
                                    </div>
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">–</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
