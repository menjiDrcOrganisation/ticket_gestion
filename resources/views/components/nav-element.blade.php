@props(['title', 'action', 'active' => false, 'icon' => 'home'])

@php
    $iconComponent = "heroicon-o-" . $icon;

    $baseClasses = "flex items-center gap-3 px-3 py-2 rounded-xl border transition-all duration-200";

    $activeClasses = $active
        ? "border-red-200 bg-red-50 text-red-700 shadow-sm"
        : "border-transparent text-gray-700 hover:border-gray-200 hover:bg-gray-100";
@endphp

<a href="{{ $action }}" class="{{ $baseClasses }} {{ $activeClasses }}">
    <div class="h-8 w-8 rounded-lg flex items-center justify-center {{ $active ? 'bg-red-100' : 'bg-gray-100' }}">
        <x-dynamic-component :component="$iconComponent" class="h-5 w-5 
            {{ $active ? 'text-red-600' : 'text-red-500' }}" />
    </div>

    <span class="font-medium text-sm">
        {{ $title }}
    </span>
</a>
