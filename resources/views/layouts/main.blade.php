@extends('layouts.app') @section('nav-elements')
   <x-nav-element 
    title="Tableau de bord" 
    action="{{ route('dashboard.admin.viewDash') }}" 
    :active="request()->routeIs('dashboard.admin.viewDash')" 
    icon="home"
/>

<x-nav-element 
    :active="request()->routeIs('evenements.index')" 
    title="Événements" 
    action="{{ route('evenements.index') }}" 
    icon="calendar"
/>

<x-nav-element 
    :active="request()->routeIs('type_billet.index')" 
    title="Type billet" 
    action="{{ route('type_billet.index') }}" 
    icon="ticket"
/>

<x-nav-element 
    title="Demande événement" 
    action="{{ route('demandeEvenement.index') }}" 
    :active="request()->routeIs('demandeEvenement.index')" 
    icon="inbox"
/>

<x-nav-element 
    title="Portefeuilles" 
    action="{{ route('portefeulle.showMontantEvent') }}" 
    :active="request()->routeIs('portefeulle.showMontantEvent')" 
    icon="wallet"
/>

<x-nav-element 
    title="Demande retrait" 
    action="{{ route('dmd_retrait.index') }}" 
    :active="request()->routeIs('dmd_retrait.index')" 
    icon="arrow-down-circle"
/>

<x-nav-element 
    title="Gestion utilisateur" 
    action="{{ route('user.index') }}" 
    :active="request()->routeIs('user.index')" 
    icon="users"
/>

    @endsection @section('content')
    @yield('content')
@endsection
