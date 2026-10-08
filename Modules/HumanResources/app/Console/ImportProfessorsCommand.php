<?php

namespace Modules\HumanResources\Console;

use Illuminate\Console\Command;
use Modules\HumanResources\Actions\SyncProfessors;

/**
 * Carica l'elenco dei professori da un file di testo (un nome completo per riga).
 * Alternativa manuale allo scraping finché non c'è una fonte automatica.
 */
class ImportProfessorsCommand extends Command
{
    protected $signature = 'hr:import-professors {file : File di testo con un nome completo per riga}';

    protected $description = 'Importa l\'elenco dei professori da file (sostituisce quello attuale)';

    public function handle(SyncProfessors $sync): int
    {
        $path = (string) $this->argument('file');

        if (! is_readable($path)) {
            $this->components->error("File non leggibile: {$path}");

            return self::FAILURE;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $saved = $sync->handle($lines);

        $this->components->info("Professori salvati: {$saved}");

        return self::SUCCESS;
    }
}
