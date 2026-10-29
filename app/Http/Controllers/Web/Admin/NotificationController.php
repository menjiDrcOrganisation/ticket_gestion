<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evenement;
use App\Models\NotificationCampaign;
use App\Models\NotificationGroup;
use App\Models\Organisateur;
use App\Services\NotificationDispatchService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class NotificationController extends Controller
{
    public function index()
    {
        return view('admin.notifications.index', [
            'campaigns' => NotificationCampaign::with(['recipients', 'event', 'group'])
                ->latest()
                ->paginate(15),
            'groups' => NotificationGroup::orderBy('name')->get(),
            'events' => Evenement::orderByDesc('date_debut')->get(['id', 'nom', 'date_debut']),
        ]);
    }

    public function store(Request $request, NotificationDispatchService $dispatch)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'subject' => ['nullable', 'string', 'max:191'],
            'message' => ['required', 'string', 'max:5000'],
            'channels' => ['required', 'array', 'min:1'],
            'channels.*' => ['required', 'in:email,sms'],
            'audience' => ['required', 'in:event_buyers,organizers,existing_group,new_group'],
            'event_id' => ['nullable', 'required_if:audience,event_buyers', 'exists:evenements,id'],
            'group_id' => ['nullable', 'required_if:audience,existing_group', 'exists:notification_groups,id'],
            'group_name' => ['nullable', 'required_if:audience,new_group', 'string', 'max:150'],
            'members' => ['nullable', 'required_if:audience,new_group', 'string', 'max:30000'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
        ]);

        if (in_array('email', $validated['channels'], true) && empty($validated['subject'])) {
            throw ValidationException::withMessages(['subject' => 'Un objet est requis pour l’envoi par e-mail.']);
        }

        $group = null;
        if ($validated['audience'] === 'new_group') {
            $members = $this->parseMembers($validated['members']);
            if ($members === []) {
                throw ValidationException::withMessages(['members' => 'Ajoutez au moins un contact avec une adresse e-mail ou un numéro de téléphone.']);
            }

            $group = NotificationGroup::create([
                'name' => $validated['group_name'],
                'members' => $members,
            ]);
        }

        $event = isset($validated['event_id']) ? Evenement::findOrFail($validated['event_id']) : null;
        $recipients = match ($validated['audience']) {
            'event_buyers' => $this->eventBuyers($event),
            'organizers' => $this->organizers(),
            'existing_group' => NotificationGroup::findOrFail($validated['group_id'])->members,
            'new_group' => $group->members,
        };
        $recipients = $this->uniqueRecipients($recipients);

        if ($recipients === []) {
            throw ValidationException::withMessages(['audience' => 'Aucun destinataire disponible pour ce groupe.']);
        }

        $campaign = $dispatch->createCampaign([
            'name' => $validated['name'],
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'channels' => array_values(array_unique($validated['channels'])),
            'trigger' => 'manual',
            'scheduled_at' => $validated['scheduled_at'] ?? null,
            'created_by' => $request->user()->id,
            'event_id' => $event?->id,
            'notification_group_id' => $group?->id ?? ($validated['group_id'] ?? null),
        ], $recipients);

        return redirect()->route('admin.notifications.index')
            ->with('success', $campaign->scheduled_at
                ? 'Notification planifiée pour le ' . $campaign->scheduled_at->format('d/m/Y à H:i') . '.'
                : 'Notification envoyée. Consultez le détail des envois dans l’historique.');
    }

    public function send(NotificationCampaign $campaign, NotificationDispatchService $dispatch)
    {
        if ($campaign->cancelled_at) {
            return back()->with('error', 'Cette notification a été annulée et ne peut pas être renvoyée.');
        }

        $campaign->update(['scheduled_at' => null, 'sent_at' => null]);
        $campaign->recipients()->update([
            'email_status' => in_array('email', $campaign->channels, true) ? 'pending' : 'skipped',
            'sms_status' => in_array('sms', $campaign->channels, true) ? 'pending' : 'skipped',
            'email_error' => null,
            'sms_error' => null,
        ]);
        $dispatch->send($campaign->fresh(['recipients']));

        return back()->with('success', 'Envoi ou renvoi terminé. Vérifiez le statut de chaque destinataire.');
    }

    public function cancel(NotificationCampaign $campaign)
    {
        if ($campaign->cancelled_at) {
            return back()->with('error', 'Cette notification est déjà annulée.');
        }

        $campaign->update(['cancelled_at' => now()]);

        return back()->with('success', 'Notification annulée.');
    }

    private function parseMembers(string $input): array
    {
        $members = [];
        foreach (preg_split('/\r\n|\r|\n/', $input) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $parts = array_map('trim', explode(';', $line, 3));
            [$name, $email, $phone] = array_pad($parts, 3, null);
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages(['members' => 'Adresse e-mail invalide dans la ligne : ' . $line]);
            }
            if (!$email && !$phone) {
                throw ValidationException::withMessages(['members' => 'Chaque contact doit avoir un e-mail ou un téléphone : ' . $line]);
            }

            $members[] = [
                'name' => $name !== '' ? $name : null,
                'email' => $email !== '' ? $email : null,
                'phone' => $phone !== '' ? $phone : null,
            ];
        }

        return $this->uniqueRecipients($members);
    }

    private function eventBuyers(?Evenement $event): array
    {
        if (!$event) {
            return [];
        }

        return $event->billets()->get(['nom_auteur', 'email', 'numero'])
            ->map(fn ($ticket): array => [
                'name' => $ticket->nom_auteur,
                'email' => $ticket->email,
                'phone' => $ticket->numero,
            ])->all();
    }

    private function organizers(): array
    {
        return Organisateur::with('user:id,name,email')->get()
            ->map(fn ($organizer): array => [
                'name' => $organizer->user?->name,
                'email' => $organizer->user?->email,
                'phone' => $organizer->telephone,
            ])->all();
    }

    private function uniqueRecipients(array $recipients): array
    {
        $unique = [];
        foreach ($recipients as $recipient) {
            $email = isset($recipient['email']) ? strtolower(trim((string) $recipient['email'])) : '';
            $phone = isset($recipient['phone']) ? trim((string) $recipient['phone']) : '';
            if ($email === '' && $phone === '') {
                continue;
            }

            $key = $email . '|' . $phone;
            $unique[$key] = [
                'name' => $recipient['name'] ?? null,
                'email' => $email !== '' ? $email : null,
                'phone' => $phone !== '' ? $phone : null,
            ];
        }

        return array_values($unique);
    }
}
