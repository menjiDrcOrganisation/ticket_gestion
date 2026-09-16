@props([
    'name' => null,
    'id' => null,
    'required' => false,
    'disabled' => false,
    'wrapperClass' => 'mb-3',
    'inputClass' => '',
])

@php
    $selectId = $id ?? $name;
    $baseClasses = trim('w-full rounded-xl border border-slate-200 bg-white/90 px-3.5 py-2.5 text-sm text-slate-800 shadow-sm transition duration-200 hover:border-slate-300 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 ' . $inputClass);
@endphp

<div class="{{ $wrapperClass }}">
    <select
        @if($selectId) id="{{ $selectId }}" @endif
        @if($name) name="{{ $name }}" @endif
        @required($required)
        @disabled($disabled)
        {{ $attributes->merge(['class' => $baseClasses]) }}
    >
        {{ $slot }}
    </select>
</div>
