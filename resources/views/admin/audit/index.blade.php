@extends('layouts.main')

@section('content')
<div class="bg-white w-full px-6 py-6 mx-auto rounded-2xl shadow">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between border-b pb-4">
        <h1 class="text-2xl font-semibold text-slate-800">Journal d'audit</h1>
        <p class="text-sm text-slate-500">Actions critiques effectuees dans l'application</p>
    </div>

    <form method="GET" action="{{ route('admin.audit.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3 mt-5">
        <x-app-select name="action" wrapperClass="">
            <option value="">Toutes les actions</option>
            @foreach($actions as $action)
                <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
            @endforeach
        </x-app-select>

        <x-app-select name="entity_type" wrapperClass="">
            <option value="">Toutes les entites</option>
            @foreach($entityTypes as $entityType)
                <option value="{{ $entityType }}" @selected(request('entity_type') === $entityType)>{{ $entityType }}</option>
            @endforeach
        </x-app-select>

        <x-app-select name="user_id" wrapperClass="">
            <option value="">Tous les utilisateurs</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>
                    {{ $user->name }} ({{ $user->email }})
                </option>
            @endforeach
        </x-app-select>

        <x-app-input type="date" name="date_from" :value="request('date_from')" wrapperClass="" />
        <x-app-input type="date" name="date_to" :value="request('date_to')" wrapperClass="" />

        <div class="md:col-span-5 flex gap-2">
            <button type="submit" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm text-white hover:bg-emerald-500">Filtrer</button>
            <a href="{{ route('admin.audit.index') }}" class="rounded-lg bg-slate-200 px-4 py-2 text-sm text-slate-800 hover:bg-slate-300">Reinitialiser</a>
        </div>
    </form>

    <div class="mt-6">
        <x-app-table minWidth="1100px" tableClass="[&>tbody>tr:hover]:bg-slate-50" stickyHeader="true">
            <x-slot:head>
                <tr>
                    <x-app-th>#</x-app-th>
                    <x-app-th>Date</x-app-th>
                    <x-app-th>Utilisateur</x-app-th>
                    <x-app-th>Action</x-app-th>
                    <x-app-th>Entité</x-app-th>
                    <x-app-th>Route</x-app-th>
                    <x-app-th>Statut</x-app-th>
                    <x-app-th>Détails</x-app-th>
                </tr>
            </x-slot:head>

            <x-slot:body>
                @forelse($logs as $log)
                    <tr class="align-top transition">
                        <x-app-td :nowrap="true">{{ ($logs->firstItem() ?? 0) + $loop->index }}</x-app-td>
                        <x-app-td :nowrap="true">{{ $log->created_at?->format('d/m/Y H:i:s') }}</x-app-td>
                        <x-app-td :nowrap="true">{{ $log->user?->name ?? 'Système' }}</x-app-td>
                        <x-app-td>{{ $log->action }}</x-app-td>
                        <x-app-td>{{ $log->entity_type ?? '-' }}{{ $log->entity_id ? ' #'.$log->entity_id : '' }}</x-app-td>
                        <x-app-td>{{ $log->route_name ?? '-' }}</x-app-td>
                        <x-app-td>{{ $log->status_code ?? '-' }}</x-app-td>
                        <x-app-td class="text-xs text-slate-600">
                            <div><strong>Méthode:</strong> {{ $log->method }}</div>
                            <div class="truncate max-w-xs"><strong>URL:</strong> {{ $log->url }}</div>
                            <div><strong>IP:</strong> {{ $log->ip_address ?? '-' }}</div>
                        </x-app-td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-6 text-center text-slate-500">Aucune entrée d'audit pour ces filtres.</td>
                    </tr>
                @endforelse
            </x-slot:body>
        </x-app-table>
    </div>

    <div class="mt-4">
        {{ $logs->links() }}
    </div>
</div>
@endsection
