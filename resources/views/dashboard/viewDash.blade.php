@extends('layouts.main')
@section('content')

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <x-indic-dashboard :value="$evenementsEncours+$evenementsPasses" title="total des evenements">
        </x-indic-dashboard>

         <x-indic-dashboard :value="$evenementsEncours" title="Événements en cours">
        </x-indic-dashboard>
        
         <x-indic-dashboard :value="$evenementsPasses" title="Événements passés">
        </x-indic-dashboard>

</div>

<script>
    lucide.createIcons();
</script>


@endsection