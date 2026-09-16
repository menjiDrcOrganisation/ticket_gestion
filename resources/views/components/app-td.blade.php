@props([
    'align' => 'left',
    'nowrap' => false,
    'class' => '',
])

@php
    $alignClass = match ($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };

    $baseClasses = trim('px-4 py-3 text-sm text-slate-700 ' . $alignClass . ' ' . ($nowrap ? 'whitespace-nowrap' : '') . ' ' . $class);
@endphp

<td {{ $attributes->merge(['class' => $baseClasses]) }}>
    {{ $slot }}
</td>
