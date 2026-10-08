<?php

namespace Modules\HumanResources\Actions;

use App\Models\Professor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\HumanResources\Support\UnipaIdentity;

/**
 * Sostituisce l'elenco dei professori (core.professors) con quello appena letto dalla fonte.
 * Se la fonte non restituisce nomi non tocca nulla: meglio un elenco vecchio che nessun elenco.
 */
class SyncProfessors
{
    /**
     * @param  iterable<string>  $fullNames
     * @return int Numero di professori salvati
     */
    public function handle(iterable $fullNames): int
    {
        $now = now();
        $rows = collect($fullNames)
            ->map(fn (string $name): string => trim((string) preg_replace('/\s+/', ' ', $name)))
            ->filter()
            ->unique()
            ->map(fn (string $name): array => [
                'id' => (string) Str::uuid7(),
                'full_name' => $name,
                'normalized_name' => UnipaIdentity::normalize($name),
                'scraped_at' => $now,
            ])
            ->values();

        if ($rows->isEmpty()) {
            return 0;
        }

        DB::transaction(function () use ($rows): void {
            Professor::query()->delete();

            foreach ($rows->chunk(500) as $chunk) {
                Professor::query()->insert($chunk->all());
            }
        });

        return $rows->count();
    }
}
