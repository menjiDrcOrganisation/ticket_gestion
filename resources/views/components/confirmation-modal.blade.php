@props(['id', 'title', 'message', 'confirmText' => 'Confirmer', 'cancelText' => 'Annuler'])

<div 
    id="{{ $id }}" 
    class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
>
    <div class="bg-white w-full max-w-md p-6 rounded shadow">

        <h2 class="text-xl font-bold">{{ $title }}</h2>
        <p class="mt-2">{{ $message }}</p>

        <div class="flex justify-end space-x-2 mt-5">
            <button 
                class="px-4 py-2 bg-gray-400 text-white rounded" 
                onclick="hideModal('{{ $id }}')"
            >
                {{ $cancelText }}
            </button>

            <button 
                class="px-4 py-2 bg-red-600 text-white rounded"
                id="{{ $id }}-confirm-btn"
            >
                {{ $confirmText }}
            </button>
        </div>

    </div>
</div>

