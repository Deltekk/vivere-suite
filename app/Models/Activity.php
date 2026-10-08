<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

/**
 * Voce dell'audit log (core.activity_log), registrata da spatie/laravel-activitylog.
 *
 * Append-only a livello di DB (vedi la migration). Ogni voce viene scritta anche sul file
 * di log "audit" (requisito "log memorizzati in OS"), conservato per lo stesso periodo.
 * Leggibile solo dai super admin (Resource "Audit log" nel panel HR).
 */
class Activity extends SpatieActivity
{
    use HasUuids;

    protected $table = 'core.activity_log';

    protected static function booted(): void
    {
        static::created(function (self $activity): void {
            Log::channel('audit')->info($activity->description, [
                'id' => $activity->id,
                'log' => $activity->log_name,
                'event' => $activity->event,
                'subject' => $activity->subject_type ? "{$activity->subject_type}:{$activity->subject_id}" : null,
                'causer' => $activity->causer_type ? "{$activity->causer_type}:{$activity->causer_id}" : null,
                'changes' => $activity->attribute_changes?->toArray(),
                'properties' => $activity->properties?->toArray(),
            ]);
        });
    }
}
