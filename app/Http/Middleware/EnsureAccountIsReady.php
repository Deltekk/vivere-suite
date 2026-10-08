<?php

namespace App\Http\Middleware;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Closure;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Modules\HumanResources\Support\AcademicYear;
use Modules\HumanResources\Support\UnipaIdentity;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prima di usare qualsiasi piattaforma l'account deve essere "pronto". In ordine:
 *
 *  1. accettato da uno staffer          -> altrimenti schermata di attesa (HR 2.2.1);
 *  2. se staff, profilo staff completo  -> codice fiscale, luogo di nascita, scuola (HR 2.2.1);
 *  3. mail istituzionale UNIPA          -> chi non è più alle superiori deve cambiarla (HR 2.2.1);
 *  4. a ottobre, anno di corso confermato (HR 2.2.1).
 *
 * Le pagine di destinazione sono nel panel HR. Gira solo sulle pagine dei panel (non sulle
 * richieste Livewire), quindi i form delle pagine di destinazione funzionano normalmente.
 */
class EnsureAccountIsReady
{
    /** Rotte sempre raggiungibili (pagine di destinazione, profilo, uscita). */
    private const ALWAYS_ALLOWED = [
        'filament.hr.pages.in-attesa',
        'filament.hr.pages.profilo-staff',
        'filament.hr.pages.conferma-anno',
        'filament.hr.auth.profile',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Filament::auth()->user();

        if (! $user instanceof User || $this->isAllowedRoute($request)) {
            return $next($request);
        }

        $target = $this->requiredStep($user);

        if ($target === null) {
            return $next($request);
        }

        if ($target === 'filament.hr.auth.profile') {
            Notification::make()
                ->title('Aggiorna la tua mail')
                ->body('Ora che sei all\'università devi usare la mail istituzionale UNIPA.')
                ->warning()
                ->send();
        }

        return redirect()->route($target);
    }

    /**
     * Nome della rotta del passo da completare, o null se l'account è pronto.
     */
    private function requiredStep(User $user): ?string
    {
        if ($user->status === UserStatus::Pending) {
            return 'filament.hr.pages.in-attesa';
        }

        if ($user->hasRoleAtLeast(Role::Staff) && ($user->staffProfile === null || $user->school_id === null)) {
            return 'filament.hr.pages.profilo-staff';
        }

        if (! $user->course_year->isHighSchool() && ! UnipaIdentity::isInstitutionalEmail($user->email)) {
            return 'filament.hr.auth.profile';
        }

        if ($user->status === UserStatus::Active
            && AcademicYear::isConfirmationPeriod()
            && ($user->course_year_confirmed_at === null || $user->course_year_confirmed_at->lessThan(AcademicYear::currentStart()))) {
            return 'filament.hr.pages.conferma-anno';
        }

        return null;
    }

    private function isAllowedRoute(Request $request): bool
    {
        $route = (string) $request->route()?->getName();

        return in_array($route, self::ALWAYS_ALLOWED, true)
            || str_contains($route, '.auth.'); // logout, verifica email, 2FA, cambio email
    }
}
