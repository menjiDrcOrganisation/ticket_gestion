@extends('layouts.app') @section('nav-elements')
@if(auth()->user()->role === 'scanneur')

    <x-nav-element 
        title="Scanner"
        :active="request()->routeIs('scanneur.showScanner')" 
        action="{{ route('scanneur.showScanner') }}"
        icon="camera"
    >
    </x-nav-element>

{{-- Sinon : afficher le menu complet --}}
@else

<x-nav-element
    title="Tableau de bord"
    action="{{ route('dashboard_orginasateur.show') }}"
    :active="request()->routeIs('dashboard_orginasateur.show')"
    icon="home"
/>

<x-nav-element
    title="Info billets"
    action="{{ route('billet.all') }}"
    :active="request()->routeIs('billet.all')"
    icon="document-text"
/>

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

<x-nav-element
    title="Événement scanner"
    action="{{ route('event_scanner.index') }}"
    :active="request()->routeIs('event_scanner.index')"
    icon="check-circle"
/>

@endif
@endsection

@section('content')
    @yield('content')
@endsection
