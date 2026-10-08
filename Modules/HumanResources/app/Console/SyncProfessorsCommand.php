<?php

namespace Modules\HumanResources\Console;

use App\Enums\Role;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\HumanResources\Actions\SyncProfessors;
use Modules\HumanResources\Contracts\ProfessorSource;
use Throwable;

/**
 * Aggiorna l'elenco dei professori dalla fonte configurata (job schedulato, D12).
 * È isolato: se fallisce non blocca nulla, scrive nel log e avvisa i super admin.
 */
class SyncProfessorsCommand extends Command
{
    protected $signature = 'hr:sync-professors';

    protected $description = 'Aggiorna l\'elenco dei professori dell\'ateneo (per gli avvisi in registrazione)';

    public function handle(ProfessorSource $source, SyncProfessors $sync): int
    {
        try {
            $saved = $sync->handle($source->fullNames());
        } catch (Throwable $exception) {
            Log::error('Sincronizzazione professori fallita', ['exception' => $exception]);

            Notification::make()
                ->title('Sincronizzazione dei professori fallita')
                ->body('L\'elenco precedente resta valido. Dettagli nel log dell\'applicazione.')
                ->danger()
                ->sendToDatabase(User::query()->where('role', Role::SuperAdmin)->get());

            $this->components->error('Sincronizzazione fallita: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->components->info($saved > 0
            ? "Professori salvati: {$saved}"
            : 'La fonte non ha restituito nomi: elenco invariato.');

        return self::SUCCESS;
    }
}
