@extends('layouts.app') @section('nav-elements')
    <x-nav-element title="Tableau de bord" action="{{ route('dashboard.admin.viewDash') }}" :active="request()->routeIs('dashboard.admin.viewDash')">
    </x-nav-element>

    <x-nav-element :active="request()->routeIs('evenements.index')" title="Evenements" action="{{ route('evenements.index') }}">
    </x-nav-element>

    <x-nav-element :active="request()->routeIs('type_billet.index')" title="Type billet" action="{{ route('type_billet.index') }}">
    </x-nav-element>

    <x-nav-element title="Demande evenement" action="{{ route('demandeEvenement.index') }}" :active="request()->routeIs('demandeEvenement.index')">
    </x-nav-element>

    <x-nav-element title="Portefeuilles" action="{{ route('portefeulle.showMontantEvent') }}" :active="request()->routeIs('portefeulle.showMontantEvent')">
    </x-nav-element>

    <x-nav-element title="Demande retrait" action="{{ route('dmd_retrait.index') }}" :active="request()->routeIs('dmd_retrait.index')">
    </x-nav-element>

    <x-nav-element title="Gestion utilisateur" action="{{ route('user.index') }}" :active="request()->routeIs('user.index')">
    </x-nav-element>
    @endsection @section('content')
    @yield('content')
@endsection
