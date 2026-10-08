<?php

namespace Modules\HumanResources\Notifications;

use App\Models\User;
use Modules\HumanResources\Filament\Resources\Users\UserResource;

/**
 * Agli admin del corso (o a tutti gli admin se il corso non ne ha): una nuova registrazione
 * aspetta di essere accettata (HR 2.2.1).
 */
class RegistrationAwaitingReview extends HumanResourcesNotification
{
    public function __construct(public User $applicant) {}

    protected function title(User $notifiable): string
    {
        return 'Nuova registrazione da accettare';
    }

    protected function lines(User $notifiable): array
    {
        $course = $this->applicant->course->name ?? $this->applicant->course_year->getLabel();

        return [
            "{$this->applicant->getFilamentName()} ({$this->applicant->username}, {$course}) si è appena registrato/a e aspetta che qualcuno dello staff accetti la richiesta.",
            'Prima di accettare controlla eventuali avvisi (somiglianza con un professore o con un utente bannato).',
        ];
    }

    protected function action(User $notifiable): ?array
    {
        return ['Rivedi la registrazione', UserResource::getUrl('view', ['record' => $this->applicant], panel: 'hr')];
    }
}
