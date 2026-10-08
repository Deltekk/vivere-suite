<?php

namespace Modules\HumanResources\Filament\Widgets;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\Course;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Modules\HumanResources\Filament\Resources\Courses\CourseResource;
use Modules\HumanResources\Filament\Resources\Users\UserResource;

/**
 * Riepilogo per lo staff nella dashboard HR: lavoro da fare (registrazioni, anni da
 * confermare) e corsi senza amministratore (ne serve almeno uno per corso, HR 2.2.4).
 */
class HumanResourcesStats extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    public static function canView(): bool
    {
        return (bool) Filament::auth()->user()?->hasRoleAtLeast(Role::Staff);
    }

    protected function getStats(): array
    {
        $pending = User::query()->where('status', UserStatus::Pending)->count();
        $toConfirm = User::query()->where('status', UserStatus::ToConfirm)->count();
        $coursesWithoutAdmins = Course::query()->doesntHave('administrators')->count();

        return [
            Stat::make('Registrazioni da accettare', $pending)
                ->color($pending > 0 ? 'warning' : 'success')
                ->url(UserResource::getUrl('index', ['tab' => 'pending'])),
            Stat::make('Anni da confermare', $toConfirm)
                ->url(UserResource::getUrl('index', ['tab' => 'to_confirm'])),
            Stat::make('Utenti attivi', User::query()->where('status', UserStatus::Active)->whereNull('anonymized_at')->count()),
            Stat::make('Corsi senza amministratore', $coursesWithoutAdmins)
                ->description($coursesWithoutAdmins > 0 ? 'Ogni corso deve avere almeno un admin' : null)
                ->color($coursesWithoutAdmins > 0 ? 'danger' : 'success')
                ->url(CourseResource::getUrl('index')),
        ];
    }
}
