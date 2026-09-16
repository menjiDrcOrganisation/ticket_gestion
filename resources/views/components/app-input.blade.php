@props([
    'name' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'wrapperClass' => 'mb-3',
    'inputClass' => '',
])

@php
    $inputId = $id ?? $name;
    $resolvedValue = $name ? old($name, $value) : $value;
    $shouldRenderValue = !in_array($type, ['password', 'file'], true) && $resolvedValue !== null;

    $baseClasses = trim('w-full rounded-xl border border-slate-200 bg-white/90 px-3.5 py-2.5 text-sm text-slate-800 shadow-sm transition duration-200 placeholder:text-slate-400 hover:border-slate-300 focus:border-emerald-500 focus:outline-none focus:ring-4 focus:ring-emerald-100 disabled:cursor-not-allowed disabled:border-slate-200 disabled:bg-slate-100 disabled:text-slate-400 readonly:bg-slate-50 ' . $inputClass);
@endphp

<div class="{{ $wrapperClass }}">
    <input
        @if($inputId) id="{{ $inputId }}" @endif
        @if($name) name="{{ $name }}" @endif
        type="{{ $type }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @if($shouldRenderValue) value="{{ $resolvedValue }}" @endif
        @required($required)
        @disabled($disabled)
        @readonly($readonly)
        {{ $attributes->merge(['class' => $baseClasses]) }}
    >
</div>
