@props([
    'name' => null,
    'id' => null,
    'required' => false,
    'disabled' => false,
    'accept' => null,
    'wrapperClass' => 'mb-3',
    'inputClass' => '',
])

@php
    $fileId = $id ?? $name;
    $baseClasses = trim('w-full border border-slate-300 rounded px-3 py-2 text-sm file:mr-3 file:rounded file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-slate-700 hover:file:bg-slate-200 ' . $inputClass);
@endphp

<div class="{{ $wrapperClass }}">
    <input
        type="file"
        @if($fileId) id="{{ $fileId }}" @endif
        @if($name) name="{{ $name }}" @endif
        @if($accept) accept="{{ $accept }}" @endif
        @required($required)
        @disabled($disabled)
        {{ $attributes->merge(['class' => $baseClasses]) }}
    >
</div>
