@extends('layouts.app') @section('nav-elements')
    <div class="mb-4">
        <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Pilotage</p>

        <x-nav-element
            title="Tableau de bord"
            action="{{ route('dashboard.admin.viewDash') }}"
            :active="request()->routeIs('dashboard.admin.viewDash')"
            icon="home"
        />

        @if(auth()->user()?->isSuperAdmin())
            <x-nav-element
                title="Journal d'audit"
                action="{{ route('admin.audit.index') }}"
                :active="request()->routeIs('admin.audit.*')"
                icon="document-text"
            />
        @endif
    </div>

    <div class="mb-4">
        <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Billetterie</p>

        <x-nav-element
            :active="request()->routeIs('evenements.*')"
            title="Événements"
            action="{{ route('evenements.index') }}"
            icon="calendar"
        />

        <x-nav-element
            :active="request()->routeIs('type_billet.*')"
            title="Type billet"
            action="{{ route('type_billet.index') }}"
            icon="ticket"
        />

        <x-nav-element
            :active="request()->routeIs('transactions.*')"
            title="Transactions"
            action="{{ route('transactions.index') }}"
            icon="banknotes"
        />
    </div>

    <div class="mb-4">
        <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Opérations</p>

        <x-nav-element
            title="Demande événement"
            action="{{ route('demandeEvenement.index') }}"
            :active="request()->routeIs('demandeEvenement.*')"
            icon="inbox"
        />

        <x-nav-element
            title="Demande retrait"
            action="{{ route('dmd_retrait.index') }}"
            :active="request()->routeIs('dmd_retrait.*')"
            icon="arrow-down-circle"
        />

        <x-nav-element
            title="Portefeuilles"
            action="{{ route('portefeulle.showMontantEvent') }}"
            :active="request()->routeIs('portefeulle.*')"
            icon="wallet"
        />
    </div>

    <div>
        <p class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-400">Administration</p>

        <x-nav-element
            title="Gestion utilisateur"
            action="{{ route('user.index') }}"
            :active="request()->routeIs('user.*')"
            icon="users"
        />
    </div>

    @endsection @section('content')
    @yield('content')
@endsection
