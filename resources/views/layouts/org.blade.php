@extends('layouts.app') @section('nav-elements')

<x-nav-element
    title="Tableau de board"
    action="{{ route('dashboard_orginasateur.show') }}"
    :active="request()->routeIs('dashboard_orginasateur.show')"
>
</x-nav-element>

<x-nav-element title="Info billets" :active="request()->routeIs('billet.all')" action="{{ route('billet.all') }}">
</x-nav-element>

<x-nav-element title="Scanner" :active="request()->routeIs('scanneur.showScanner')" action="{{ route('scanneur.showScanner') }}">
</x-nav-element>

<x-nav-element title="Demande retrait" :active="request()->routeIs('retrait.index')" action="{{ route('retrait.index') }}">
</x-nav-element>

<x-nav-element
    title="Événement scanner"
    action="{{ route('event_scanner.index') }}"
    :active="request()->routeIs('event_scanner.index')"
>
</x-nav-element>

@endsection @section('content') @yield('content') @endsection
