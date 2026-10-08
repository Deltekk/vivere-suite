<?php

/*
|--------------------------------------------------------------------------
| Impostazioni comuni della Suite Vivere
|--------------------------------------------------------------------------
|
| Valori validi per tutti i moduli. Le impostazioni di un singolo modulo
| stanno nel suo config (Modules/<Modulo>/config/config.php).
|
*/

return [

    /*
     | Fuso orario in cui MOSTRARE le date e in cui calcolare la logica "di calendario"
     | (compleanni, inizio settimana, ottobre, ...). Il DB e l'applicazione restano in UTC.
     */
    'display_timezone' => env('APP_DISPLAY_TIMEZONE', 'Europe/Rome'),

    /*
     | Versione dell'informativa privacy da far accettare in registrazione (core.users.terms_version).
     | Se cambia, agli utenti va richiesto di accettare la nuova versione.
     */
    'terms_version' => env('VIVERE_TERMS_VERSION', '2026-10'),

    /*
     | Pagina dei suggerimenti linkata da ogni piattaforma (requisito del preambolo).
     | Se è vuota, il link non viene mostrato.
     */
    'suggestions_url' => env('VIVERE_SUGGESTIONS_URL'),

    /*
     | Conservazione dei log di audit, in giorni. Il preambolo chiede 2 anni (730 giorni).
     | Non c'è un massimo di legge; il minimo per i log di accesso degli amministratori
     | di sistema è 6 mesi (Garante privacy, provv. 27/11/2008). Vedi CLAUDE.md.
     */
    'audit_retention_days' => (int) env('VIVERE_AUDIT_RETENTION_DAYS', 730),

    /*
     | Servizi della suite per cui l'utente può scegliere se ricevere email
     | (core.email_preferences.service, alias del modulo => nome mostrato).
     */
    'services' => [
        'humanresources' => 'Risorse Umane (avvisi sul tuo account e comunicazioni dell\'associazione)',
        'kaffettino' => 'Kaffettino',
        'drive' => 'Drive',
        'eventi' => 'Eventi',
        'orientamento' => 'Orientamento',
        'oggettismarriti' => 'Oggetti smarriti',
        'segnalazioni' => 'Segnalazioni',
        'magazzino' => 'Magazzino',
        'assistest' => 'Assistest',
    ],

    /*
     | Indirizzo di Mailpit, che in sviluppo cattura tutte le mail in uscita.
     | Lo usa "php artisan vivere:mail" (scheda "mail" di composer dev).
     */
    'mailpit_url' => env('MAILPIT_URL', 'http://localhost:'.env('FORWARD_MAILPIT_DASHBOARD_PORT', '8025')),

    /*
     | Super admin creato da SuperAdminSeeder: il documento prevede che esista fin
     | dall'installazione e che non si possa revocare. Se la password è vuota, il seeder
     | ne genera una casuale e la stampa una sola volta.
     */
    'super_admin' => [
        'name' => env('SUPERADMIN_NAME', 'Super'),
        'surname' => env('SUPERADMIN_SURNAME', 'Admin'),
        'username' => env('SUPERADMIN_USERNAME', 'Super.Admin'),
        'email' => env('SUPERADMIN_EMAIL'),
        'password' => env('SUPERADMIN_PASSWORD'),
        'phone_number' => env('SUPERADMIN_PHONE', '-'),
        'birthday' => env('SUPERADMIN_BIRTHDAY', '2000-01-01'),
    ],

];
