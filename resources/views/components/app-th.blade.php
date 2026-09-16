@props([
    'align' => 'left',
    'class' => '',
])

@php
    $alignClass = match ($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };

    $baseClasses = trim('px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-600 ' . $alignClass . ' ' . $class);
@endphp

<th {{ $attributes->merge(['class' => $baseClasses]) }}>
    {{ $slot }}
</th>
