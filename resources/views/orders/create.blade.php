{{--
    @var \Illuminate\Support\Collection<\App\Models\User> $users
    @var \Illuminate\Support\Collection<\App\Models\Dish> $dishes
    @var string $dateMenu
    @var bool $canDisplayForm
    @var bool $isOpen
    @var ?int $selectedUserId
--}}
@extends('layouts.app')

@push('scripts')
    <script src="{{ asset('js/commande.js') }}" defer></script>
@endpush

@section('content')
<div class="flex items-start justify-center px-4 py-10">
    @if ($isOpen && $canDisplayForm)

        <div class="w-full max-w-lg">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl dark:shadow-gray-900/50 overflow-hidden">

                <!-- Header -->
                <div class="relative bg-gradient-to-br from-indigo-600 to-indigo-500 px-6 py-5 overflow-hidden">
                    <div class="absolute -top-6 -right-6 w-28 h-28 bg-white/5 rounded-full pointer-events-none"></div>
                    <div class="absolute -bottom-8 -left-4 w-20 h-20 bg-white/5 rounded-full pointer-events-none"></div>
                    <div class="relative flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                            <i class="bi bi-bag-heart-fill text-white text-lg"></i>
                        </div>
                        <div>
                            <h1 class="text-white font-bold text-lg leading-tight">Passe ta commande</h1>
                            <p class="text-indigo-200 text-xs mt-0.5">Lundi · {{ $dateMenu }}</p>
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form action="{{ route('orders.store') }}" id="order-form" method="POST"
                      class="divide-y divide-gray-100 dark:divide-gray-700/60">
                    @csrf

                    <!-- Qui commande -->
                    <div class="px-6 py-5">
                        <label for="user-select"
                               class="block text-[11px] font-bold uppercase tracking-widest
                                      text-gray-400 dark:text-gray-500 mb-2.5">
                            Qui commande ?
                        </label>
                        <div class="relative">
                            <i class="bi bi-person-fill absolute left-3.5 top-1/2 -translate-y-1/2
                                      text-indigo-400 dark:text-indigo-500 pointer-events-none text-sm"></i>
                            <select id="user-select" name="user" required
                                    class="appearance-none w-full pl-10 pr-9 py-3
                                           bg-gray-50 dark:bg-gray-700
                                           border-2 border-gray-200 dark:border-gray-600
                                           text-gray-900 dark:text-gray-100 text-sm
                                           rounded-xl transition-colors cursor-pointer
                                           focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                <option value="" disabled @selected($selectedUserId === null) hidden>
                                    Sélectionner ton nom…
                                </option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected((int) $user->id === (int) $selectedUserId)>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                            <i class="bi bi-chevron-down absolute right-3.5 top-1/2 -translate-y-1/2
                                      text-gray-400 text-xs pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Plats -->
                    <div class="px-6 py-5">
                        <p class="text-[11px] font-bold uppercase tracking-widest
                                  text-gray-400 dark:text-gray-500 mb-3">
                            Tes plats
                        </p>
                        <div class="space-y-2" id="dishes-list">
                            @foreach ($dishes as $dish)
                                <div class="dish-card flex items-center gap-4
                                            bg-gray-50 dark:bg-gray-700/50
                                            border-2 border-transparent
                                            rounded-xl px-4 py-3 transition-all duration-150 select-none"
                                     data-dish-id="{{ $dish->id }}">

                                    <div class="dish-toggle flex items-center gap-3 flex-1 min-w-0 cursor-pointer py-0.5">
                                        <i class="dish-icon bi bi-circle
                                                  text-gray-300 dark:text-gray-600
                                                  flex-shrink-0 text-base transition-all"></i>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <x-category-badge :category="$dish->category" />
                                                <span class="text-sm font-medium
                                                             text-gray-700 dark:text-gray-300 leading-snug">
                                                    {{ $dish->name }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Stepper -->
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <button type="button"
                                                class="decrement w-7 h-7 rounded-lg flex items-center justify-center
                                                       bg-white dark:bg-gray-600
                                                       border border-gray-200 dark:border-gray-500
                                                       text-gray-400 dark:text-gray-400
                                                       hover:bg-red-50 hover:border-red-200 hover:text-red-500
                                                       dark:hover:bg-red-900/20 dark:hover:border-red-800 dark:hover:text-red-400
                                                       transition-colors">
                                            <i class="bi bi-dash text-sm"></i>
                                        </button>
                                        <span class="qty-display w-6 text-center text-sm font-bold
                                                     text-gray-400 dark:text-gray-500 transition-colors">0</span>
                                        <button type="button"
                                                class="increment w-7 h-7 rounded-lg flex items-center justify-center
                                                       bg-indigo-50 dark:bg-indigo-900/30
                                                       border border-indigo-200 dark:border-indigo-800
                                                       text-indigo-500 dark:text-indigo-400
                                                       hover:bg-indigo-100 hover:border-indigo-300
                                                       dark:hover:bg-indigo-900/50
                                                       transition-colors">
                                            <i class="bi bi-plus text-sm"></i>
                                        </button>
                                        <input type="hidden" name="dishes[{{ $dish->id }}]" value="0">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Commentaires -->
                    <div class="px-6 py-5">
                        <label for="perso"
                               class="block text-[11px] font-bold uppercase tracking-widest
                                      text-gray-400 dark:text-gray-500 mb-2.5">
                            Commentaires
                            <span class="font-normal normal-case tracking-normal"> — optionnel</span>
                        </label>
                        <textarea name="perso" id="perso" rows="2"
                                  placeholder="Ex : sans sauce, bien cuit…"
                                  class="w-full bg-gray-50 dark:bg-gray-700
                                         border-2 border-gray-200 dark:border-gray-600
                                         text-gray-900 dark:text-gray-100
                                         placeholder-gray-400 dark:placeholder-gray-500
                                         text-sm rounded-xl resize-none p-3 transition-colors
                                         focus:outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"></textarea>
                    </div>

                    <!-- Submit -->
                    <div class="px-6 py-5 bg-gray-50/50 dark:bg-gray-700/20">
                        <button type="submit"
                                class="w-full flex items-center justify-center gap-2
                                       bg-indigo-600 hover:bg-indigo-700 active:scale-[0.98]
                                       text-white font-semibold rounded-xl py-3.5 text-sm
                                       transition-all shadow-lg shadow-indigo-500/25">
                            <i class="bi bi-send-fill"></i>
                            Valider ma commande
                        </button>
                    </div>
                </form>
            </div>
        </div>

    @else

        <div class="flex flex-col items-center justify-center gap-4 py-20 text-center">
            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-2xl
                        flex items-center justify-center shadow-sm">
                <i class="bi bi-clock-history text-gray-400 dark:text-gray-500 text-2xl"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-700 dark:text-gray-300 text-base">
                    @if (!$isOpen)
                        Nourriture terrestre est fermé cette semaine
                    @else
                        Commandes fermées pour le moment
                    @endif
                </p>
                @if ($isOpen)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1.5">
                        La prise de commande est ouverte le lundi de 0h à 11h15
                    </p>
                @endif
            </div>
        </div>

    @endif
</div>
@endsection
