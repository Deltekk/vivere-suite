<?php

namespace App\Models;

use App\Enums\DeviceType;
use App\Models\Concerns\Audited;
use Carbon\CarbonImmutable;
use Database\Factories\DeviceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * Dispositivo embedded della suite (core.devices), gestito dalla "fleet" nel pannello.
 *
 * @property string $id
 * @property string $name
 * @property DeviceType $type
 * @property string $token_hash
 * @property string|null $admin_code
 * @property CarbonImmutable|null $admin_code_changed_at
 * @property CarbonImmutable|null $config_synced_at
 * @property string|null $firmware_version
 * @property CarbonImmutable|null $last_seen_at
 * @property string|null $last_ip
 * @property CarbonImmutable|null $revoked_at
 * @property string $auletta_id
 */
#[Table('core.devices')]
#[Fillable(['name', 'type', 'auletta_id'])]
#[Hidden(['token_hash', 'admin_code'])]
class Device extends Model
{
    /** @use HasFactory<DeviceFactory> */
    use Audited, HasFactory, HasUuids;

    protected function casts(): array
    {
        return [
            'type' => DeviceType::class,
            // Cifrato con APP_KEY: nel DB non è mai in chiaro, ma l'applicazione lo può rileggere
            'admin_code' => 'encrypted',
            'admin_code_changed_at' => 'datetime',
            'config_synced_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Auletta, $this> */
    public function auletta(): BelongsTo
    {
        return $this->belongsTo(Auletta::class);
    }

    /**
     * Genera un nuovo token API e ne salva solo l'hash. Restituisce il token in chiaro,
     * da mostrare UNA volta sola all'admin per configurare il dispositivo.
     */
    public function issueToken(): string
    {
        $token = Str::random(48);
        $this->token_hash = self::hashToken($token);

        return $token;
    }

    /**
     * Hash con cui il token viene salvato e cercato (SHA-256: deterministico, quindi indicizzabile;
     * va bene perché il token è lungo e casuale, a differenza di una password).
     */
    public static function hashToken(string $token): string
    {
        return hash('sha256', $token);
    }

    /**
     * Imposta un nuovo codice della modalità amministratore. Il dispositivo lo riceverà alla
     * prossima sincronizzazione (finché config_synced_at < admin_code_changed_at è "in attesa").
     */
    public function changeAdminCode(string $code): void
    {
        $this->admin_code = $code;
        $this->admin_code_changed_at = now();
    }

    /**
     * Hash del codice admin inviato al dispositivo, che lo usa per verificare il codice offline.
     * Il sale è l'id del dispositivo: lo stesso codice dà hash diversi su dispositivi diversi.
     */
    public function adminCodeHashForDevice(): ?string
    {
        return $this->admin_code === null ? null : hash('sha256', $this->id.'|'.$this->admin_code);
    }

    /**
     * Vero se il dispositivo non ha ancora scaricato l'ultimo codice admin impostato.
     */
    public function isAdminCodeSyncPending(): bool
    {
        return $this->admin_code_changed_at !== null
            && ($this->config_synced_at === null || $this->config_synced_at->lessThan($this->admin_code_changed_at));
    }
}
