<?php

namespace Modules\HumanResources\Contracts;

/**
 * Fonte dei nomi dei professori dell'ateneo, usati per avvisare lo staff quando chi si registra
 * ha lo stesso nome di un professore (HR 2.2.1, D12).
 *
 * L'implementazione si sceglie in HumanResourcesServiceProvider. Un'implementazione che fa
 * scraping deve lanciare un'eccezione se la pagina cambia formato, invece di restituire un
 * elenco vuoto: un elenco vuoto non cancella i professori già salvati.
 */
interface ProfessorSource
{
    /**
     * Nomi completi dei professori ("Mario Rossi" o "ROSSI Mario": l'ordine non conta).
     *
     * @return iterable<string>
     */
    public function fullNames(): iterable;
}
