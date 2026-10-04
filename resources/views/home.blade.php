{{--
    @var \App\Models\Menu $menu
    @var bool $canDisplayForm
    @var string $dateFormatee
    @var array $dishesWithCategory
    @var \Illuminate\Support\Collection<\App\Models\Announcement> $announcements
    @var bool $showTicker
--}}
@extends('layouts.app')

@section('content')

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- Ticker Nouveautés                                              -->
<!-- ══════════════════════════════════════════════════════════════ -->
@if ($showTicker)
<div class="bg-slate-800 dark:bg-slate-950 border-b border-slate-700 dark:border-slate-800 overflow-hidden">
    <div class="flex items-stretch">
        <div class="flex-shrink-0 flex items-center bg-amber-500 px-4 py-2.5">
            <span class="text-xs font-bold uppercase tracking-widest text-white">Nouveautés</span>
        </div>
        <div class="overflow-hidden flex-1">
            <div class="ticker-track flex items-center gap-16 py-2.5 text-slate-300 text-sm whitespace-nowrap">
                {{-- Deux copies pour une boucle infinie --}}
                @foreach ([1, 2] as $copy)
                    @foreach ($announcements as $announcement)
                        <span>📣&nbsp;{{ $announcement->message }}</span>
                    @endforeach
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif

<!-- ══════════════════════════════════════════════════════════════ -->
<!-- Contenu principal                                              -->
<!-- ══════════════════════════════════════════════════════════════ -->
@if ($menu->is_open)
    <div class="max-w-screen-xl mx-auto px-4 py-10">

        <div class="text-center mb-10">
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-500 dark:text-indigo-400 mb-2">
                Cette semaine
            </p>
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white capitalize">
                {{ $dateFormatee }}
            </h1>
            <div class="mt-3 w-16 h-1 bg-indigo-500 mx-auto rounded-full"></div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8 items-start justify-center">

            <!-- ── Plats ── -->
            <div class="w-full lg:w-1/2 space-y-1">
                @foreach ($dishesWithCategory as $i => $item)
                    @php $cat = $item['cat']; @endphp
                    @if ($item['showDivider'])
                        <div class="flex items-center gap-3 {{ $i > 0 ? 'mt-6' : '' }} mb-3">
                            <div class="flex-1 border-t {{ $cat['dividerLine'] }}"></div>
                            <span class="text-xs font-bold uppercase tracking-widest {{ $cat['divider'] }}">
                                {{ $cat['icon'] }}&nbsp;{{ $cat['label'] }}
                            </span>
                            <div class="flex-1 border-t {{ $cat['dividerLine'] }}"></div>
                        </div>
                    @endif

                    <div class="group flex items-center gap-4
                            bg-white dark:bg-gray-800
                            border-l-4 {{ $cat['accent'] }}
                            px-5 py-4 rounded-xl
                            shadow-sm hover:shadow-md hover:-translate-y-0.5
                            transition-all duration-200 cursor-default">
                        <span class="text-xl flex-shrink-0">{{ $cat['icon'] }}</span>
                        <p class="text-gray-800 dark:text-gray-100 font-medium text-base leading-snug flex-1">
                            {{ $item['dish']->name }}
                        </p>
                        <span class="text-[10px] font-semibold uppercase tracking-wider px-2 py-0.5
                                 rounded-full opacity-0 group-hover:opacity-100 transition-opacity
                                 {{ $cat['badge'] }}">
                            {{ $cat['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>

            <!-- ── Image ── -->
            <div class="w-full lg:w-5/12 lg:sticky lg:top-20">
                @include('partials.menu-image', ['imageClass' => 'w-full object-cover', 'imageStyle' => 'max-height: 520px;'])
            </div>

        </div>
    </div>

@elseif ($canDisplayForm)
    <div class="max-w-screen-xl mx-auto px-4 py-10">
        <div class="lg:w-5/12 mx-auto">
            @include('partials.menu-image', ['imageClass' => 'w-full object-cover max-h-96', 'imageStyle' => null])
        </div>
    </div>
@else
    <div class="max-w-screen-xl mx-auto px-4 mt-6">
        <div class="p-4 text-sm text-blue-800 dark:text-blue-300
                    rounded-lg bg-blue-50 dark:bg-blue-900/20
                    border border-blue-200 dark:border-blue-800 text-center">
            Pas de nourriture terrestre aujourd'hui
        </div>
    </div>
@endif

<style>
    .ticker-track {
        animation: ticker 32s linear infinite;
        display: inline-flex;
    }

    @keyframes ticker {
        from {
            transform: translateX(0);
        }
        to {
            transform: translateX(-50%);
        }
    }
</style>
@endsection
