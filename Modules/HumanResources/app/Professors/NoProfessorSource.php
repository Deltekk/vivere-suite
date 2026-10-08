<?php

namespace Modules\HumanResources\Professors;

use Modules\HumanResources\Contracts\ProfessorSource;

/**
 * Fonte provvisoria: non restituisce nessun nome.
 *
 * TODO: individuare la pagina dell'ateneo con l'elenco dei docenti e scrivere lo scraper
 * (la pagina www.unipa.it/persone/docenti/ non contiene l'elenco nell'HTML). Nel frattempo
 * l'elenco si può caricare da file con "php artisan hr:import-professors".
 */
class NoProfessorSource implements ProfessorSource
{
    public function fullNames(): iterable
    {
        return [];
    }
}
