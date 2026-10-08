<?php

namespace Modules\HumanResources\Actions;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Modules\HumanResources\Notifications\BroadcastMessage;

/**
 * Un admin invia una comunicazione a molti utenti insieme (HR 2.2.4).
 * Destinatari: utenti attivi, eventualmente filtrati per ruolo, corso e anno di corso.
 * Le notifiche vanno in coda, quindi l'invio non blocca la pagina.
 */
class SendBroadcast
{
    /**
     * @param  array{roles?: list<string>, courses?: list<string>, course_years?: list<string>}  $audience
     * @return int Numero di destinatari
     */
    public function handle(string $subject, string $body, array $audience, User $sentBy): int
    {
        Gate::forUser($sentBy)->authorize('broadcast', User::class);

        $recipients = $this->recipients($audience);
        $count = $recipients->count();

        $recipients->with('emailPreferences')->chunkById(200, function ($users) use ($subject, $body): void {
            Notification::send($users, new BroadcastMessage($subject, $body));
        });

        activity('humanresources')->causedBy($sentBy)->event('broadcast')
            ->withProperties(['subject' => $subject, 'audience' => $audience, 'recipients' => $count])
            ->log('Notifica inviata a più utenti');

        return $count;
    }

    /**
     * @param  array{roles?: list<string>, courses?: list<string>, course_years?: list<string>}  $audience
     * @return Builder<User>
     */
    public function recipients(array $audience): Builder
    {
        $roles = $audience['roles'] ?? [];
        $courses = $audience['courses'] ?? [];
        $courseYears = $audience['course_years'] ?? [];

        return User::query()
            ->where('status', UserStatus::Active)
            ->whereNull('anonymized_at')
            ->when($roles !== [], fn (Builder $query) => $query->whereIn('role', array_map(fn (string $role) => Role::from($role), $roles)))
            ->when($courses !== [], fn (Builder $query) => $query->whereIn('course_id', $courses))
            ->when($courseYears !== [], fn (Builder $query) => $query->whereIn('course_year', $courseYears));
    }
}
