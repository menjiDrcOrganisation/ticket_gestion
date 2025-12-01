@props(['title', 'action', 'active' => false, 'icon' => 'home'])

@php
    $iconComponent = "heroicon-o-" . $icon;

    $baseClasses = "flex items-center gap-3 px-3 py-2 rounded-xl transition-all duration-200";

    $activeClasses = $active
        ? "bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-md scale-[1.02]"
        : "text-gray-700 hover:bg-gray-100 hover:scale-[1.02]";
@endphp

<a href="{{ $action }}" class="{{ $baseClasses }} {{ $activeClasses }}">
    <div class="h-8 w-8 bg-white/20 rounded-lg flex items-center justify-center">
        <x-dynamic-component :component="$iconComponent" class="h-5 w-5 
            {{ $active ? 'text-white' : 'text-purple-700' }}" />
    </div>

    <span class="font-medium text-sm">
        {{ $title }}
    </span>
</a>
