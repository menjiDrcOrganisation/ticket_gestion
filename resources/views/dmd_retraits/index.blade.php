
@extends('layouts.main')
@section('content')
<div class="bg-white w-full px-6 py-6 mx-auto mt-6">
    <div class="flex flex-wrap -mx-3">
        <div class="flex-none w-full max-w-full px-3">
            <div class="relative flex flex-col mb-6 bg-white shadow-xl rounded-2xl">
                <div class="p-6 border-b rounded-t-2xl flex items-center justify-between">
                    <h6 class="text-xl font-semibold">Demandes de retraits</h6>
                </div>

                <div class="flex-auto px-0 pt-4 pb-2">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse text-gray-800">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">N°</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Utilisateur</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Montant</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Moyen</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold uppercase">Statut</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold uppercase">Date</th>
                                    <th class="px-6 py-3 text-center text-xs font-bold uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dmd_retraits as $withdrawal)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">{{ $loop->iteration }}</td>
                                        <td class="px-6 py-4">{{ $withdrawal->user->name ?? $withdrawal->user_name ?? '—' }}</td>
                                        <td class="px-6 py-4">{{ number_format($withdrawal->amount, 2, ',', ' ') }} €</td>
                                        <td class="px-6 py-4">{{ $withdrawal->method ?? '—' }}</td>
                                        <td class="px-6 py-4">{{ ucfirst($withdrawal->status) }}</td>
                                        <td class="px-6 py-4 text-center">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-6 py-4 text-center">
                                            <button type="button" onclick="showModal('viewWithdraw{{ $withdrawal->id }}')" class="px-3 py-1 bg-indigo-600 text-white rounded text-sm">Voir</button>
                                            @if($withdrawal->status === 'pending')
                                                <form action="{{ route('withdrawals.update', $withdrawal->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded text-sm ml-2">Approuver</button>
                                                </form>
                                                <button type="button" onclick="showModal('rejectWithdraw{{ $withdrawal->id }}')" class="px-3 py-1 bg-red-600 text-white rounded text-sm ml-2">Refuser</button>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- View Modal -->
                                    <div id="viewWithdraw{{ $withdrawal->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                        <div class="bg-white rounded-lg shadow-lg w-96 p-6">
                                            <h3 class="text-lg font-semibold mb-3">Détails du retrait</h3>
                                            <p><strong>Utilisateur :</strong> {{ $withdrawal->user->name ?? $withdrawal->user_name ?? '—' }}</p>
                                            <p><strong>Montant :</strong> {{ number_format($withdrawal->amount, 2, ',', ' ') }} €</p>
                                            <p><strong>Méthode :</strong> {{ $withdrawal->method ?? '—' }}</p>
                                            <p class="mb-4"><strong>Statut :</strong> {{ ucfirst($withdrawal->status) }}</p>
                                            <div class="flex justify-end gap-2">
                                                <button type="button" onclick="hideModal('viewWithdraw{{ $withdrawal->id }}')" class="px-4 py-2 bg-gray-300 rounded">Fermer</button>
                                                @if($withdrawal->status === 'pending')
                                                    <form action="{{ route('withdrawals.update', $withdrawal->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="status" value="approved">
                                                        <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Approuver</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reject Modal -->
                                    <div id="rejectWithdraw{{ $withdrawal->id }}" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                                        <div class="bg-white rounded-lg shadow-lg w-96 p-6">
                                            <h3 class="text-lg font-semibold mb-3">Refuser la demande</h3>
                                            <form action="{{ route('withdrawals.update', $withdrawal->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="rejected">
                                                <label class="block mb-2 text-sm">Motif (optionnel)</label>
                                                <textarea name="reason" rows="3" class="w-full border rounded px-3 py-2 mb-4" placeholder="Raison du refus"></textarea>
                                                <div class="flex justify-end gap-2">
                                                    <button type="button" onclick="hideModal('rejectWithdraw{{ $withdrawal->id }}')" class="px-4 py-2 bg-gray-300 rounded">Annuler</button>
                                                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Confirmer le refus</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-gray-500">Aucune demande de retrait.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showModal(id) {
        const el = document.getElementById(id);
        if (el) el.classList.remove('hidden');
    }
    function hideModal(id) {
        const el = document.getElementById(id);
        if (el) el.classList.add('hidden');
    }
</script>