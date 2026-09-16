
@props([
    'title',
    'value',
    'subtitle' => null,
    'icon' => 'chart-bar',
    'tone' => 'blue',
    'href' => null,
])

@php
    $tones = [
        'blue' => [
            'ring' => 'ring-blue-100',
            'iconBg' => 'bg-blue-50',
            'iconText' => 'text-blue-600',
            'badge' => 'bg-blue-50 text-blue-700',
        ],
        'emerald' => [
            'ring' => 'ring-emerald-100',
            'iconBg' => 'bg-emerald-50',
            'iconText' => 'text-emerald-600',
            'badge' => 'bg-emerald-50 text-emerald-700',
        ],
        'amber' => [
            'ring' => 'ring-amber-100',
            'iconBg' => 'bg-amber-50',
            'iconText' => 'text-amber-600',
            'badge' => 'bg-amber-50 text-amber-700',
        ],
        'rose' => [
            'ring' => 'ring-rose-100',
            'iconBg' => 'bg-rose-50',
            'iconText' => 'text-rose-600',
            'badge' => 'bg-rose-50 text-rose-700',
        ],
        'slate' => [
            'ring' => 'ring-slate-100',
            'iconBg' => 'bg-slate-50',
            'iconText' => 'text-slate-600',
            'badge' => 'bg-slate-50 text-slate-700',
        ],
    ];

    $palette = $tones[$tone] ?? $tones['blue'];
    $iconComponent = 'heroicon-o-' . $icon;
@endphp

@if($href)
    <a href="{{ $href }}" aria-label="Accéder à {{ $title }}" class="group block rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 {{ $palette['ring'] }} transition duration-200 hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-300">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-slate-500">{{ $title }}</p>
                <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-800">{{ $value }}</p>
                @if($subtitle)
                    <p class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $palette['badge'] }}">
                        {{ $subtitle }}
                    </p>
                @endif

                <p class="mt-3 inline-flex items-center gap-1 text-sm font-semibold {{ $palette['iconText'] }}">
                    Accès rapide
                    <x-heroicon-o-arrow-right class="h-4 w-4" />
                </p>
            </div>

            <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl {{ $palette['iconBg'] }} {{ $palette['iconText'] }}">
                <x-dynamic-component :component="$iconComponent" class="h-5 w-5" />
            </div>
        </div>
    </a>
@else
    <div class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm ring-1 {{ $palette['ring'] }} transition duration-200 hover:-translate-y-0.5 hover:shadow-md">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-sm font-medium text-slate-500">{{ $title }}</p>
                <p class="mt-2 text-3xl font-extrabold tracking-tight text-slate-800">{{ $value }}</p>
                @if($subtitle)
                    <p class="mt-2 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $palette['badge'] }}">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>

            <div class="inline-flex h-11 w-11 items-center justify-center rounded-xl {{ $palette['iconBg'] }} {{ $palette['iconText'] }}">
                <x-dynamic-component :component="$iconComponent" class="h-5 w-5" />
            </div>
        </div>
    </div>
@endif
