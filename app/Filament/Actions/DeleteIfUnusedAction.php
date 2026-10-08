<?php

namespace App\Filament\Actions;

use Filament\Actions\DeleteAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Elimina un dato di riferimento solo se nessun altro dato lo usa.
 *
 * Le FK del DB non hanno cascade (es. un edificio con delle aule non si cancella): se Postgres
 * rifiuta la cancellazione (errore 23503, foreign_key_violation) mostriamo un avviso invece di
 * una pagina di errore. La cancellazione gira in una transazione annidata (savepoint), così un
 * rifiuto non invalida la transazione esterna.
 */
final class DeleteIfUnusedAction
{
    public static function make(): DeleteAction
    {
        return DeleteAction::make()
            ->using(function (Model $record): bool {
                try {
                    return (bool) DB::transaction(fn () => $record->delete());
                } catch (QueryException $exception) {
                    if ($exception->getCode() === '23503') {
                        return false;
                    }

                    throw $exception;
                }
            })
            ->failureNotificationTitle('Non si può eliminare: è ancora collegato ad altri dati');
    }
}
