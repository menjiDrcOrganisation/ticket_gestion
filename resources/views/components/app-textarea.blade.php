@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'placeholder' => null,
    'rows' => 3,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'wrapperClass' => 'mb-3',
    'inputClass' => '',
])

@php
    $textareaId = $id ?? $name;
    $resolvedValue = $name ? old($name, $value) : $value;
    $baseClasses = trim('w-full border border-slate-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-400 focus:outline-none ' . $inputClass);
@endphp

<div class="{{ $wrapperClass }}">
    <textarea
        @if($textareaId) id="{{ $textareaId }}" @endif
        @if($name) name="{{ $name }}" @endif
        rows="{{ $rows }}"
        @if($placeholder) placeholder="{{ $placeholder }}" @endif
        @required($required)
        @disabled($disabled)
        @readonly($readonly)
        {{ $attributes->merge(['class' => $baseClasses]) }}
    >{{ $resolvedValue }}</textarea>
</div>
