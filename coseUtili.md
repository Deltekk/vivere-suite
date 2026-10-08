# Cose utili

## Avvio del progetto

### Prima installazione

Servono PHP 8.5 (con `pdo_pgsql`, `redis`, `pcntl`), Composer, Node 22+ e Docker.

```shell
composer setup
```

Fa, in ordine: `composer install`, copia `.env.example` in `.env` (se manca), genera `APP_KEY` (solo se manca), avvia i servizi Docker, esegue migration e seed e infine `npm install` e `npm run build`.

Durante il seed viene creato il super admin definito in `SUPERADMIN_EMAIL`. Se `SUPERADMIN_PASSWORD` è vuota viene generata una password casuale, **stampata una sola volta** nel terminale.

### Sviluppo quotidiano

```shell
composer dev
```

È l'unico comando da lanciare. Avvia i servizi Docker (Postgres, Redis, Mailpit), aspetta che siano pronti e poi lancia `php artisan dev`, che apre in un solo terminale:

| Processo | Cosa fa |
|---|---|
| `server` | `php artisan serve` → http://localhost:8000 |
| `queue` | esegue le code (mail, notifiche, export) |
| `scheduler` | esegue i job schedulati ogni minuto (come il cron in produzione) |
| `logs` | log dell'applicazione in tempo reale (Pail) |
| `mail` | mostra ogni mail catturata da Mailpit appena arriva, con il testo (codici 2FA, notifiche, ...) |
| `vite` | hot reload di CSS/JS |

Indirizzi utili:
- http://localhost:8000/hr → piattaforma HR (login di tutta la suite)
- http://localhost:8000/kaffettino → piattaforma Kaffettino (solo staff attivo)
- http://localhost:8025 → **Mailpit**: tutte le mail inviate in sviluppo, compresi i codici 2FA

### Leggere le mail dal terminale

In sviluppo nessuna mail parte davvero: le cattura Mailpit. Oltre alla scheda `mail` di `composer dev`:

```shell
php artisan vivere:mail --latest   # testo dell'ultima mail (es. il codice 2FA appena richiesto)
php artisan vivere:mail            # elenco delle ultime mail: scegli quale leggere (frecce + invio)
```

Le schede di `composer dev` non ricevono input da tastiera, quindi l'elenco interattivo va lanciato da un secondo terminale. Il codice 2FA scade dopo 4 minuti e se ne possono richiedere al massimo 2 al minuto: vale solo l'ultimo ricevuto.

Utenti finti creati dal seed: password `password`. Per vederli: `php artisan tinker` e poi `User::all(['email', 'role', 'status'])`.

Per fermare tutto: `Ctrl+C`, poi `composer services:stop` se vuoi spegnere anche i container.

> Lo sviluppo è "ibrido": PHP e Node girano sul tuo PC, i servizi in Docker. Per questo in `.env` gli host sono `127.0.0.1` e non `pgsql`/`redis`/`mailpit`.

### Processi aggiuntivi in `artisan dev`

Si registrano in `App\Providers\AppServiceProvider::configureDevCommands()`:

```php
DevCommands::artisan('reverb:start', 'reverb');
```

`php artisan dev:list` mostra i processi registrati.

## Database

```shell
php artisan migrate                 # applica le migration nuove
php artisan migrate:fresh --seed    # SOLO in locale: cancella tutto e ricrea con dati finti
php artisan db:seed --class=SuperAdminSeeder   # crea/aggiorna il super admin (anche in produzione);
                                               # se SUPERADMIN_PASSWORD è impostata, la riapplica
php artisan db                      # apre psql sul database
```

Lo schema completo è in `database/database.dbml`. Si apre con l'estensione VS Code "DBML ERD Previewer" o su dbdiagram.io. Va **sempre** tenuto allineato alle migration.

Convenzioni (dettagli in `CLAUDE.md`):
- schemi: `public` per le tabelle tecniche di Laravel, `core` per le entità condivise, uno schema per ogni modulo con tabelle proprie;
- nel codice le tabelle si scrivono sempre con lo schema: `Schema::create('core.users', ...)`, `#[Table('core.users')]`, `constrained('core.users')`;
- nelle regole di validazione si passa la classe del model, **non** `core.tabella`: `Rule::unique(User::class, 'email')`. Laravel leggerebbe `core` come nome della connessione.

## HR

```shell
php artisan hr:advance-academic-year       # passaggio al nuovo anno accademico (automatico il 1° ottobre)
php artisan hr:import-professors file.txt  # elenco professori da file, un nome per riga (per gli avvisi in registrazione)
php artisan hr:sync-professors             # aggiorna i professori dalla fonte configurata (automatico ogni settimana)
```

Primo accesso del super admin: siccome è almeno admin, gli vengono chiesti codice fiscale, luogo di nascita e scuola (profilo staff). Se è ottobre, gli viene chiesta anche la conferma dell'anno di corso.

L'audit log si consulta da `/hr/audit-log` (solo super admin). Le stesse voci sono su file in `storage/logs/audit-AAAA-MM-GG.log`.

## Moduli

È stato utilizzato nwidart/laravel-modules per creare i moduli.

Per creare un nuovo modulo usare solo nomi senza trattini ed in CamelCase!

```shell
php artisan module:make nomeModulo
php artisan module:enable nomeModulo
php artisan module:migrate nomeModulo
```

Grazie agli stub in `stubs/nwidart-stubs` il modulo nasce già senza `vite.config.js`/`package.json` propri (il build è unico) e senza rotte d'esempio. Per dargli un'interfaccia:

1. copiare `Modules/Kaffettino/app/Providers/Filament/KaffettinoPanelProvider.php` nel nuovo modulo, rinominando classe, `id`, `path`, `brandName` e il nome del modulo in `discoverModuleComponents()`;
2. aggiungere il PanelProvider all'array `$providers` del `<Modulo>ServiceProvider`;
3. aggiungere la regola di accesso del panel in `User::canAccessPanel()`: senza regola l'accesso è negato.

Se il modulo ha tabelle proprie, la sua prima migration crea lo schema (`CREATE SCHEMA IF NOT EXISTS nomemodulo`) e lo schema va aggiunto a `DB_SEARCH_PATH` (default in `config/database.php`).

Comandi utili:

```shell
php artisan module:list                       # moduli e stato
php artisan module:make-model NomeModel Modulo -mf   # model + migration + factory dentro il modulo
php artisan make:filament-resource NomeModel --panel=hr   # CRUD Filament: lo crea direttamente nel modulo del panel
```

Le Resource, le pagine e i widget Filament di un modulo vanno in `Modules/<Modulo>/app/Filament/{Resources,Pages,Widgets}`: il panel li trova da solo.

## Model nel core

Per creare un nuovo model **condiviso** (in `app/Models`, tabella nello schema `core`):

``` shell
php artisan make:model nomeModel -mfs
```

Dove le flag:
- m: genera la migration
- f: genera la factory
- s: genera il seeder

Dopo la generazione: rinominare la tabella in `core.<nome>` nella migration, aggiungere `#[Table('core.<nome>')]` e `use HasUuids` al model.

Attenzione: `--all` (o `-a`) **non** equivale a `-msfc`: genera anche policy, form request e un controller resource. Con Filament controller e form request non servono, quindi meglio non usarlo.

## Qualità del codice

```shell
composer lint         # sistema lo stile (Pint)
composer test         # stile + analisi statica (PHPStan livello 7) + test Pest
php artisan test      # solo i test
composer types:check  # solo PHPStan (con il limite di memoria già impostato)
```

I test girano sul database `testing` del container Postgres (creato in automatico da Sail al primo avvio del volume), quindi non toccano i tuoi dati di sviluppo. I test dei moduli vanno in `Modules/<Modulo>/tests/Unit` e `Modules/<Modulo>/tests/Feature` e girano insieme agli altri.

> `php artisan migrate:fresh --seed` ricrea anche il super admin: imposta `SUPERADMIN_PASSWORD` nel `.env`, altrimenti ogni volta ne viene generata una nuova.

## Docker

```shell
composer services        # avvia Postgres, Redis, Mailpit e aspetta che siano pronti
composer services:stop   # li ferma
docker compose ps        # stato dei container
docker compose logs -f pgsql
```
