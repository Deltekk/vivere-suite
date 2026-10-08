<?php

namespace Database\Seeders;

use App\Enums\CourseYear;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Crea (o aggiorna) il super admin definito nelle variabili SUPERADMIN_* del .env.
 *
 * Il documento chiede che il super admin esista fin dall'installazione e non sia revocabile.
 * Idempotente: se l'utente esiste già (stessa email) aggiorna ruolo e stato e, se
 * SUPERADMIN_PASSWORD è impostata, anche la password (utile per reimpostarla in locale).
 * Se SUPERADMIN_PASSWORD è vuota e l'utente non esiste, genera una password casuale e la
 * stampa UNA sola volta.
 */
class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $config = config('vivere.super_admin');

        if (blank($config['email'])) {
            $this->command->warn('SUPERADMIN_EMAIL non impostata: super admin non creato.');

            return;
        }

        $user = User::firstWhere('email', Str::lower($config['email']));

        if ($user === null) {
            $password = $config['password'] ?: Str::password(20);

            $user = new User([
                'name' => $config['name'],
                'surname' => $config['surname'],
                'username' => $config['username'],
                'birthday' => $config['birthday'],
                'course_year' => CourseYear::Graduating,
                'phone_number' => $config['phone_number'],
                'email' => $config['email'],
                'password' => $password,
                'privacy_accepted_at' => now(),
                'terms_version' => config('vivere.terms_version'),
            ]);
            $user->email_verified_at = now();

            if (blank($config['password'])) {
                $this->command->warn("Password generata per il super admin {$config['email']}: {$password}");
            }
        } elseif (filled($config['password'])) {
            $user->password = $config['password'];
        }

        $user->role = Role::SuperAdmin;
        $user->status = UserStatus::Active;
        $user->save();
    }
}
