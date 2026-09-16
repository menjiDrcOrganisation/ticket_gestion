@props([
    'minWidth' => '920px',
    'wrapperClass' => '',
    'tableClass' => '',
    'striped' => false,
    'stickyHeader' => false,
])

@php
    $wrapperClasses = trim('rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden ' . $wrapperClass);
    $scrollClasses = 'overflow-x-auto';
    $tableClasses = trim('w-full border-separate border-spacing-0 text-sm text-slate-700 ' . $tableClass);
    $theadClasses = trim('bg-slate-100/90 text-slate-700 ' . ($stickyHeader ? 'sticky top-0 z-10' : ''));
@endphp

<div {{ $attributes->merge(['class' => $wrapperClasses]) }}>
    <div class="{{ $scrollClasses }}">
        <table class="{{ $tableClasses }}" style="min-width: {{ $minWidth }};">
            <thead class="{{ $theadClasses }}">
                {{ $head }}
            </thead>

            <tbody class="divide-y divide-slate-100 {{ $striped ? 'odd:[&>tr:nth-child(odd)]:bg-slate-50/40' : '' }}">
                {{ $body }}
            </tbody>
        </table>
    </div>
</div>
