<?php

namespace App\Notifications;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Base di tutte le notifiche della suite: arrivano sempre in piattaforma (campanella di Filament,
 * core.notifications) e per mail se l'utente le vuole per quel servizio (core.email_preferences).
 * Le notifiche obbligatorie (stato dell'account, chiavi dell'auletta, ...) arrivano sempre per mail.
 *
 * Ogni notifica concreta definisce title(), lines() e service(); il resto è uguale per tutte.
 * Vanno in coda (ShouldQueue): in sviluppo le esegue il processo "queue" di composer dev.
 */
abstract class SuiteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Titolo: oggetto della mail e titolo nella campanella.
     */
    abstract protected function title(User $notifiable): string;

    /**
     * Paragrafi del testo.
     *
     * @return list<string>
     */
    abstract protected function lines(User $notifiable): array;

    /**
     * Alias del modulo che invia la notifica (es. "humanresources"), per le preferenze email.
     */
    abstract protected function service(): string;

    /**
     * Vero per le notifiche che arrivano per mail anche a chi ha disattivato le email del servizio.
     */
    protected function isMandatory(): bool
    {
        return false;
    }

    /**
     * Pulsante facoltativo: [etichetta, url].
     *
     * @return array{0: string, 1: string}|null
     */
    protected function action(User $notifiable): ?array
    {
        return null;
    }

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        $sendMail = $this->isMandatory() || $notifiable->wantsEmailsFor($this->service());

        return $sendMail ? ['database', 'mail'] : ['database'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->title($notifiable))
            ->greeting("Ciao {$notifiable->name},");

        foreach ($this->lines($notifiable) as $line) {
            $message->line($line);
        }

        if ($action = $this->action($notifiable)) {
            $message->action(...$action);
        }

        return $message;
    }

    /**
     * Formato richiesto dalla campanella di Filament.
     *
     * @return array<string, mixed>
     */
    public function toDatabase(User $notifiable): array
    {
        $notification = FilamentNotification::make()
            ->title($this->title($notifiable))
            ->body(implode("\n\n", $this->lines($notifiable)));

        if ($action = $this->action($notifiable)) {
            $notification->actions([Action::make('open')->label($action[0])->url($action[1])->markAsRead()]);
        }

        return $notification->getDatabaseMessage();
    }
}
