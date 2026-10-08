<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Registra nell'audit log ogni creazione, modifica e cancellazione del model (solo i campi
 * cambiati). I campi segreti sono esclusi globalmente in config/activitylog.php.
 *
 * Uso: `use Audited;` nel model. Il model deve avere un alias nel morph map
 * (AppServiceProvider o ServiceProvider del modulo), altrimenti il log fallisce.
 * Per gli eventi applicativi (es. un ban) si usa activity()->performedOn(...)->log(...).
 */
trait Audited
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName($this->auditLogName())
            ->logAll()
            ->logOnlyDirty()
            ->dontLogEmptyChanges();
    }

    /**
     * Nome del log: il modulo a cui appartiene il model ("core" per quelli condivisi).
     */
    protected function auditLogName(): string
    {
        return preg_match('/^Modules\\\\([^\\\\]+)\\\\/', static::class, $matches) === 1
            ? strtolower($matches[1])
            : 'core';
    }
}
