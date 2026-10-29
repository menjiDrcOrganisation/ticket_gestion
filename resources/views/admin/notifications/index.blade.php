@extends('layouts.main')

@section('title', 'Notifications')

@section('content')
<div class="mx-auto max-w-7xl space-y-8">
    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-xl font-semibold text-slate-900">Créer une notification</h2>
        <p class="mt-1 text-sm text-slate-500">Envoyez un message par e-mail, SMS ou les deux, immédiatement ou à une date choisie.</p>

        @if($errors->any())
            <div class="mt-4 rounded-lg bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-inside list-disc">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.notifications.store') }}" class="mt-6 grid gap-5 md:grid-cols-2">
            @csrf
            <label class="text-sm font-medium text-slate-700">Nom de la notification
                <input name="name" required maxlength="150" value="{{ old('name') }}" class="mt-1 w-full rounded-lg border-slate-300">
            </label>
            <label class="text-sm font-medium text-slate-700">Objet de l’e-mail
                <input name="subject" maxlength="191" value="{{ old('subject') }}" class="mt-1 w-full rounded-lg border-slate-300">
            </label>
            <label class="text-sm font-medium text-slate-700">Canaux
                <span class="mt-2 flex gap-5">
                    <span><input type="checkbox" name="channels[]" value="email" @checked(in_array('email', old('channels', ['email', 'sms'])))> E-mail</span>
                    <span><input type="checkbox" name="channels[]" value="sms" @checked(in_array('sms', old('channels', ['email', 'sms'])))> SMS</span>
                </span>
            </label>
            <label class="text-sm font-medium text-slate-700">Destinataires
                <select name="audience" id="notification-audience" required class="mt-1 w-full rounded-lg border-slate-300">
                    <option value="event_buyers" @selected(old('audience') === 'event_buyers')>Acheteurs d’un événement</option>
                    <option value="organizers" @selected(old('audience') === 'organizers')>Tous les organisateurs</option>
                    <option value="existing_group" @selected(old('audience') === 'existing_group')>Groupe enregistré</option>
                    <option value="new_group" @selected(old('audience') === 'new_group')>Créer et enregistrer un groupe</option>
                </select>
            </label>
            <label id="notification-event" class="text-sm font-medium text-slate-700">Événement
                <select name="event_id" class="mt-1 w-full rounded-lg border-slate-300">
                    <option value="">Choisir un événement</option>
                    @foreach($events as $event)
                        <option value="{{ $event->id }}" @selected(old('event_id') == $event->id)>{{ $event->nom }} — {{ $event->date_debut }}</option>
                    @endforeach
                </select>
            </label>
            <label id="notification-group" class="hidden text-sm font-medium text-slate-700">Groupe enregistré
                <select name="group_id" class="mt-1 w-full rounded-lg border-slate-300">
                    <option value="">Choisir un groupe</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}" @selected(old('group_id') == $group->id)>{{ $group->name }} ({{ count($group->members) }} contacts)</option>
                    @endforeach
                </select>
            </label>
            <div id="notification-new-group" class="hidden space-y-3 md:col-span-2">
                <label class="block text-sm font-medium text-slate-700">Nom du groupe
                    <input name="group_name" maxlength="150" value="{{ old('group_name') }}" class="mt-1 w-full rounded-lg border-slate-300">
                </label>
                <label class="block text-sm font-medium text-slate-700">Contacts (une ligne par personne : nom ; e-mail ; téléphone)
                    <textarea name="members" rows="5" placeholder="Amina ; amina@example.com ; +243..." class="mt-1 w-full rounded-lg border-slate-300">{{ old('members') }}</textarea>
                </label>
            </div>
            <label class="text-sm font-medium text-slate-700 md:col-span-2">Message
                <textarea name="message" rows="5" required maxlength="5000" class="mt-1 w-full rounded-lg border-slate-300">{{ old('message') }}</textarea>
            </label>
            <label class="text-sm font-medium text-slate-700">Planifier (laisser vide pour envoyer maintenant)
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" class="mt-1 w-full rounded-lg border-slate-300">
            </label>
            <div class="flex items-end">
                <button class="rounded-lg bg-indigo-600 px-5 py-3 font-semibold text-white hover:bg-indigo-700">Enregistrer la notification</button>
            </div>
        </form>

        <p class="mt-5 rounded-lg bg-amber-50 p-3 text-sm text-amber-800">L’envoi SMS nécessite des identifiants Twilio configurés sur le serveur. Les échecs restent visibles dans l’historique.</p>
    </section>

    <section class="rounded-2xl bg-white p-6 shadow-sm">
        <h2 class="text-xl font-semibold text-slate-900">Historique des envois</h2>
        <div class="mt-5 space-y-4">
            @forelse($campaigns as $campaign)
                @php
                    $sent = $campaign->recipients->sum(fn ($recipient) => (int) ($recipient->email_status === 'sent') + (int) ($recipient->sms_status === 'sent'));
                    $failed = $campaign->recipients->sum(fn ($recipient) => (int) ($recipient->email_status === 'failed') + (int) ($recipient->sms_status === 'failed'));
                @endphp
                <article class="rounded-xl border border-slate-200 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h3 class="font-semibold text-slate-900">{{ $campaign->name }}</h3>
                            <p class="mt-1 text-sm text-slate-500">{{ $campaign->trigger }} · {{ implode(', ', $campaign->channels) }} · {{ $campaign->recipients->count() }} destinataire(s)</p>
                            <p class="mt-1 text-sm">
                                @if($campaign->cancelled_at)<span class="text-rose-700">Annulée {{ $campaign->cancelled_at->format('d/m/Y H:i') }}</span>
                                @elseif($campaign->scheduled_at && !$campaign->sent_at)<span class="text-amber-700">Planifiée le {{ $campaign->scheduled_at->format('d/m/Y H:i') }}</span>
                                @elseif($campaign->sent_at)<span class="text-emerald-700">Traitée le {{ $campaign->sent_at->format('d/m/Y H:i') }}</span>
                                @else<span class="text-slate-500">En attente</span>@endif
                                · {{ $sent }} envoyé(s) · {{ $failed }} échec(s)
                            </p>
                        </div>
                        <div class="flex gap-2">
                            @if(!$campaign->cancelled_at)
                                <form method="POST" action="{{ route('admin.notifications.send', $campaign) }}">@csrf
                                    <button class="rounded-lg border border-indigo-200 px-3 py-2 text-sm font-medium text-indigo-700 hover:bg-indigo-50">{{ $campaign->sent_at ? 'Renvoyer' : 'Envoyer maintenant' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.notifications.cancel', $campaign) }}">@csrf
                                    <button class="rounded-lg border border-rose-200 px-3 py-2 text-sm font-medium text-rose-700 hover:bg-rose-50">Annuler</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <details class="mt-4">
                        <summary class="cursor-pointer text-sm font-medium text-slate-700">Détail des envois</summary>
                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full text-left text-sm">
                                <thead><tr class="border-b text-slate-500"><th class="py-2">Destinataire</th><th>E-mail</th><th>SMS</th></tr></thead>
                                <tbody>
                                    @foreach($campaign->recipients as $recipient)
                                        <tr class="border-b border-slate-100">
                                            <td class="py-2">{{ $recipient->name ?: '—' }}<br><span class="text-xs text-slate-500">{{ $recipient->email ?: $recipient->phone }}</span></td>
                                            <td>{{ $recipient->email_status }} @if($recipient->email_error)<span class="block max-w-xs text-xs text-rose-600">{{ $recipient->email_error }}</span>@endif</td>
                                            <td>{{ $recipient->sms_status }} @if($recipient->sms_error)<span class="block max-w-xs text-xs text-rose-600">{{ $recipient->sms_error }}</span>@endif</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </details>
                </article>
            @empty
                <p class="text-sm text-slate-500">Aucune notification envoyée ou planifiée.</p>
            @endforelse
        </div>
        <div class="mt-5">{{ $campaigns->links() }}</div>
    </section>
</div>
<script>
    const audience = document.getElementById('notification-audience');
    const toggleAudienceFields = () => {
        document.getElementById('notification-event').classList.toggle('hidden', audience.value !== 'event_buyers');
        document.getElementById('notification-group').classList.toggle('hidden', audience.value !== 'existing_group');
        document.getElementById('notification-new-group').classList.toggle('hidden', audience.value !== 'new_group');
    };
    audience.addEventListener('change', toggleAudienceFields);
    toggleAudienceFields();
</script>
@endsection
