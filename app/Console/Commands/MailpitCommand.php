<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Formatter\OutputFormatter;

use function Laravel\Prompts\select;

/**
 * Legge da terminale le mail catturate da Mailpit in sviluppo (codici 2FA, notifiche, ...).
 *
 * Tre modi d'uso:
 *  - php artisan vivere:mail --watch   -> resta in ascolto e stampa ogni mail nuova con il testo
 *                                         (è la scheda "mail" di composer dev, vedi AppServiceProvider)
 *  - php artisan vivere:mail --latest  -> stampa l'ultima mail ed esce
 *  - php artisan vivere:mail           -> elenco interattivo per scegliere quale mail leggere
 *
 * Usa l'API HTTP di Mailpit (https://mailpit.axllent.org/docs/api-v1/), nessuna libreria aggiuntiva.
 */
class MailpitCommand extends Command
{
    protected $signature = 'vivere:mail
        {--watch : Resta in ascolto e mostra le mail nuove appena arrivano}
        {--latest : Mostra l\'ultima mail ricevuta ed esce}
        {--interval=2 : Secondi tra un controllo e l\'altro in modalità --watch}
        {--lines=60 : Numero massimo di righe di testo mostrate per mail}';

    protected $description = 'Mostra in forma testuale le mail catturate da Mailpit (solo sviluppo)';

    /** Diventa true quando arriva Ctrl+C / SIGTERM: --watch finisce il giro corrente ed esce. */
    private bool $stopping = false;

    public function handle(): int
    {
        // In produzione le mail partono davvero e Mailpit non esiste
        if (app()->isProduction()) {
            $this->components->error('Comando disponibile solo in sviluppo.');

            return self::FAILURE;
        }

        return match (true) {
            (bool) $this->option('watch') => $this->watch(),
            (bool) $this->option('latest') => $this->showLatest(),
            default => $this->browse(),
        };
    }

    /**
     * Controlla Mailpit ogni --interval secondi e stampa le mail arrivate dopo l'avvio,
     * finché non viene fermato (Ctrl+C o chiusura di composer dev).
     */
    private function watch(): int
    {
        // Uscita pulita su Ctrl+C / SIGTERM. Senza l'estensione pcntl (es. Windows) i segnali
        // non si possono intercettare: il processo viene semplicemente terminato.
        if (extension_loaded('pcntl')) {
            $this->trap([SIGINT, SIGTERM], function (): void {
                $this->stopping = true;
            });
        }

        $this->components->info("Mailpit: {$this->mailpitUrl()} — in attesa di nuove mail");

        $seen = null;          // ID già mostrati (o già presenti all'avvio)
        $wasReachable = true;  // per segnalare una sola volta che Mailpit non risponde

        while (! $this->stopping) {
            $messages = $this->fetchMessages(50);

            if ($messages === null) {
                if ($wasReachable) {
                    $this->components->warn("Mailpit non risponde su {$this->mailpitUrl()}: riprovo... (è avviato? composer services)");
                }
                $wasReachable = false;
            } else {
                if (! $wasReachable) {
                    $this->components->info('Mailpit di nuovo raggiungibile.');
                }
                $wasReachable = true;

                if ($seen === null) {
                    // Primo giro: le mail già presenti non si ristampano, si leggono con --latest o senza opzioni
                    $seen = array_fill_keys(array_column($messages, 'ID'), true);
                    $this->line('  '.count($messages).' mail già presenti (php artisan vivere:mail per sfogliarle).');
                } else {
                    // Mailpit restituisce le mail dalla più recente: le stampiamo in ordine di arrivo
                    foreach (array_reverse($messages) as $message) {
                        if (! isset($seen[$message['ID']])) {
                            $seen[$message['ID']] = true;
                            $this->render($message['ID']);
                        }
                    }
                }
            }

            sleep(max(1, (int) $this->option('interval')));
        }

        return self::SUCCESS;
    }

    /**
     * Stampa l'ultima mail ricevuta.
     */
    private function showLatest(): int
    {
        $messages = $this->fetchMessages(1);

        if ($messages === null) {
            return $this->mailpitUnreachable();
        }

        if ($messages === []) {
            $this->components->info('Nessuna mail in Mailpit.');

            return self::SUCCESS;
        }

        $this->render($messages[0]['ID']);

        return self::SUCCESS;
    }

    /**
     * Elenco interattivo delle ultime mail: si sceglie quale leggere finché non si esce.
     */
    private function browse(): int
    {
        // Senza terminale interattivo (es. dentro una scheda di composer dev) non si può scegliere
        if (! $this->input->isInteractive()) {
            return $this->showLatest();
        }

        while (true) {
            $messages = $this->fetchMessages(30);

            if ($messages === null) {
                return $this->mailpitUnreachable();
            }

            if ($messages === []) {
                $this->components->info('Nessuna mail in Mailpit.');

                return self::SUCCESS;
            }

            $options = [];
            foreach ($messages as $message) {
                $options[$message['ID']] = sprintf(
                    '%s · %s · %s',
                    $this->formatTime($message['Created']),
                    $this->recipients($message),
                    $message['Subject'] ?: '(senza oggetto)',
                );
            }
            $options['exit'] = 'Esci';

            $choice = (string) select(label: 'Quale mail vuoi leggere?', options: $options, scroll: 12);

            if ($choice === 'exit') {
                return self::SUCCESS;
            }

            $this->render($choice);
        }
    }

    /**
     * Stampa intestazione e testo di una mail.
     */
    private function render(string $id): void
    {
        $message = $this->fetchMessage($id);

        if ($message === null) {
            $this->components->warn("Impossibile leggere la mail {$id}.");

            return;
        }

        $this->newLine();
        $this->line('<fg=gray>'.str_repeat('─', 60).'</>');
        $this->line(sprintf(
            '<fg=cyan>%s</>  <options=bold>A:</> %s  <options=bold>Da:</> %s',
            $this->formatTime($message['Date'] ?? $message['Created'] ?? null),
            $this->recipients($message),
            $message['From']['Address'] ?? '?',
        ));
        $this->line('<options=bold>Oggetto:</> '.($message['Subject'] ?: '(senza oggetto)'));
        $this->newLine();

        $lines = explode("\n", $this->plainText($message));
        $maxLines = max(1, (int) $this->option('lines'));

        foreach (array_slice($lines, 0, $maxLines) as $line) {
            // escape: il testo della mail non deve essere interpretato come tag di stile della console
            $this->line('  '.OutputFormatter::escape($line));
        }

        if (count($lines) > $maxLines) {
            $this->line('  <fg=gray>… altre '.(count($lines) - $maxLines).' righe</>');
        }

        $this->newLine();
        $this->line("<fg=gray>Apri in Mailpit: {$this->mailpitUrl()}/view/{$id}</>");
    }

    /**
     * Testo della mail: la parte text/plain se c'è, altrimenti l'HTML ripulito dai tag.
     *
     * @param  array<string, mixed>  $message
     */
    private function plainText(array $message): string
    {
        $text = trim((string) ($message['Text'] ?? ''));

        if ($text === '') {
            $text = trim(html_entity_decode(strip_tags((string) ($message['HTML'] ?? ''))));
        }

        // Normalizza gli a capo e comprime le righe vuote consecutive
        $text = (string) preg_replace("/\r\n?/", "\n", $text);

        return (string) preg_replace("/\n{3,}/", "\n\n", $text) ?: '(mail senza testo)';
    }

    /**
     * Ultime $limit mail (dalla più recente), o null se Mailpit non risponde.
     *
     * @return array<int, array<string, mixed>>|null
     */
    private function fetchMessages(int $limit): ?array
    {
        try {
            $response = Http::timeout(3)->get("{$this->mailpitUrl()}/api/v1/messages", ['limit' => $limit]);
        } catch (ConnectionException) {
            return null;
        }

        return $response->successful() ? ($response->json('messages') ?? []) : null;
    }

    /**
     * Dettaglio di una mail (testo compreso), o null se non disponibile.
     *
     * @return array<string, mixed>|null
     */
    private function fetchMessage(string $id): ?array
    {
        try {
            $response = Http::timeout(3)->get("{$this->mailpitUrl()}/api/v1/message/{$id}");
        } catch (ConnectionException) {
            return null;
        }

        return $response->successful() ? $response->json() : null;
    }

    /**
     * @param  array<string, mixed>  $message
     */
    private function recipients(array $message): string
    {
        $addresses = array_column($message['To'] ?? [], 'Address');

        return $addresses === [] ? '?' : implode(', ', $addresses);
    }

    /**
     * Data della mail nel fuso italiano (Mailpit la fornisce in UTC).
     */
    private function formatTime(?string $date): string
    {
        if (blank($date)) {
            return '--:--:--';
        }

        $time = CarbonImmutable::parse($date)->setTimezone(config('vivere.display_timezone'));

        return $time->isToday() ? $time->format('H:i:s') : $time->format('d/m H:i');
    }

    private function mailpitUnreachable(): int
    {
        $this->components->error("Mailpit non risponde su {$this->mailpitUrl()}. Avvialo con: composer services");

        return self::FAILURE;
    }

    private function mailpitUrl(): string
    {
        return rtrim((string) config('vivere.mailpit_url'), '/');
    }
}
