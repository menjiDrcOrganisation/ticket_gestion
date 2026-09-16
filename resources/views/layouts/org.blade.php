@extends('layouts.app')
@section('nav-elements')
@if(auth()->user()->role === 'scanneur')
    <div class="mb-4">
        <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Scan</p>
        <x-nav-element
            title="Scanner"
            :active="request()->routeIs('scanneur.showScanner')"
            action="{{ route('scanneur.showScanner') }}"
            icon="camera"
        />
    </div>
@else
    <div class="mb-4">
        <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Pilotage</p>
        <x-nav-element
            title="Tableau de bord"
            action="{{ route('dashboard_orginasateur.show') }}"
            :active="request()->routeIs('dashboard_orginasateur.show')"
            icon="home"
        />
    </div>

    <div class="mb-4">
        <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Billetterie</p>
        <x-nav-element
            title="Mes billets"
            action="{{ route('billet.all') }}"
            :active="request()->routeIs('billet.all')"
            icon="document-text"
        />

        <x-nav-element
            title="Mes événements"
            action="{{ route('event_scanner.index') }}"
            :active="request()->routeIs('event_scanner.index')"
            icon="check-circle"
        />
    </div>

    <div>
        <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Opérations</p>
        <x-nav-element
            title="Scanner"
            action="{{ route('scanneur.showScanner') }}"
            :active="request()->routeIs('scanneur.showScanner')"
            icon="camera"
        />

        <x-nav-element
            title="Demande retrait"
            action="{{ route('retrait.index') }}"
            :active="request()->routeIs('retrait.index')"
            icon="currency-dollar"
        />
    </div>
@endif
@endsection

@section('content')
    @yield('content')
@endsection
