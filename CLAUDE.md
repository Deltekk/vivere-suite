# CLAUDE.md — Suite Vivere

Guida per chi (persona o LLM) riprende lo sviluppo. Va letta prima di toccare il codice e aggiornata quando si prende una decisione nuova.

## Cos'è

Suite di applicativi per l'associazione Vivere (Ingegneria, UNIPA): un **monolite Laravel modulare** in cui ogni progetto della suite è un modulo `nwidart/laravel-modules` in `Modules/`.

Fonti di verità, in quest'ordine:
1. **`Documentation.pdf`** — requisiti di ogni modulo (16 progetti). Le correzioni decise dopo stanno nella sezione "Decisioni" qui sotto e prevalgono sul PDF.
2. **`database/database.dbml`** — schema del DB. Va tenuto **sempre allineato** alle migration.
3. **`stack.md`** — tecnologie e dipendenze per modulo, compresi i pacchetti scartati e il motivo.
4. **`coseUtili.md`** — comandi quotidiani.

**Ambito attuale: solo HumanResources (HR) e Kaffettino.** Gli altri 12 moduli restano abilitati ma non si sviluppano (contengono solo lo scaffolding nwidart). Mancano ancora i moduli Calendario ed Elezioni. Filigrana esiste fuori da qui ma va rifatta per standardizzarla.

Regole di lavoro chieste dal maintainer:
- se trovi incongruenze tra PDF, DBML, stack.md e codice, **segnalale prima di cambiare**;
- se qualcosa non convince, dillo subito;
- **meno librerie possibile**: aggiungine una solo se ben documentata, mantenuta, non deprecata e se accorcia davvero il codice;
- commenta il codice dove serve (in italiano, come il resto) e aggiorna `coseUtili.md` con i comandi utili.

## Stack (dettagli in stack.md)

PHP 8.5 · Laravel 13 · Livewire 4.4 · **Filament 5.10** · PostgreSQL 18 · Redis · Tailwind 4 · Vite (build unico) · Pest 4 · Larastan livello 7 · Docker (servizi di sviluppo via `compose.yaml` di Sail).

Non usiamo, di proposito: Fortify, Sanctum, spatie/laravel-permission, coolsam/modules, predis, apexcharts, maatwebsite/excel, livewire/blaze (motivi in stack.md, sezione "Rimossi o scartati").

## Avvio

```shell
composer setup   # prima volta
composer dev     # ogni giorno: Docker (pgsql, redis, mailpit) + server, queue, scheduler, log, vite
```

Sviluppo **ibrido**: PHP/Node sull'host, servizi in Docker, quindi in `.env` gli host sono `127.0.0.1`. Mail su Mailpit (http://localhost:8025), compresi i codici 2FA; da terminale con `php artisan vivere:mail` (`App\Console\Commands\MailpitCommand`, che è anche la scheda `mail` di `composer dev`). `php artisan dev` di Laravel 13 usa `@laravel/multiplex` (Linux/macOS) o `concurrently` (Windows). I processi extra si registrano in `AppServiceProvider::configureDevCommands()`.

## Architettura

### Moduli e UI ("Filament-first")

- Ogni modulo con un'interfaccia è un **panel Filament** (`/hr`, `/kaffettino`, ...).
- Tutti i panel estendono **`App\Providers\Filament\VivereSuitePanelProvider`**, che definisce una volta sola tema, colori, middleware, campanella delle notifiche, menu per passare da una piattaforma all'altra (`resources/views/filament/platform-switcher.blade.php`) e link ai suggerimenti. Il modulo implementa solo `configureModule()`. È il punto in cui si standardizza la UI: non duplicare queste configurazioni nei panel.
- Il **tema** è uno solo: `resources/css/filament/theme.css`, nel build Vite della root. I moduli **non** hanno `vite.config.js` o `package.json`.
- **Autenticazione solo in HR**: login, reset password, verifica email e 2FA via email obbligatoria. Gli altri panel e le rotte `auth` fuori dai panel rimandano al login di HR (`AuthenticateWithHumanResources` e `redirectGuestsTo` in `bootstrap/app.php`).
- **Chi entra in quale panel** lo decide `User::canAccessPanel()`: ogni nuovo panel deve avere lì la sua regola, altrimenti l'accesso è negato.
- Resource, pagine e widget di un modulo stanno in `Modules/<Modulo>/app/Filament/{Resources,Pages,Widgets}` e vengono scoperti in automatico. `php artisan make:filament-resource X --panel=hr` li crea già lì.
- Le pagine che non sono semplici gestionali (schermata di attesa, home Kaffettino col "buongiornissimo", statistiche) sono **Page Filament personalizzate** fatte con i componenti Blade di Filament (`<x-filament::section>`, `<x-filament::button>`, ...), così hanno lo stesso aspetto.
- La logica di dominio va in classi **Action** riutilizzabili (`Modules/<Modulo>/app/Actions`, es. `BanUser`, `AcceptRegistration`): le chiamano sia Filament sia, in futuro, l'API per gli embedded. Ogni Action ricontrolla la propria Policy con `Gate::forUser($chi)->authorize(...)`, quindi resta sicura anche fuori da Filament. Le azioni Filament (`UserActions`, `AulettaManagementActions`) contengono solo l'interfaccia.
- **Notifiche**: ogni notifica estende `App\Notifications\SuiteNotification`. Arriva sempre nella campanella e per mail se l'utente vuole le email di quel servizio (`User::wantsEmailsFor()`); `isMandatory()` forza la mail per stato dell'account, chiavi e sicurezza. Vanno in coda.
- **Audit**: i model condivisi usano il trait `App\Models\Concerns\Audited` (activitylog: solo i campi cambiati, mai i segreti). Gli eventi applicativi si registrano con `activity('<modulo>')->performedOn(...)->causedBy(...)->event(...)->log(...)`. Ogni model auditato deve avere un alias nel morph map.
- **Campi dei form utente** condivisi tra registrazione, gestione utenti e profilo: `Modules\HumanResources\Filament\Schemas\UserFields`. Le regole del documento (età, mail UNIPA, formato username, unicità senza maiuscole) stanno lì una volta sola.
- **Percorso dell'account** (`App\Http\Middleware\EnsureAccountIsReady`, in tutti i panel): in attesa → schermata di attesa; staff senza profilo → profilo staff; non più alle superiori ma senza mail UNIPA → profilo; a ottobre anno non confermato → conferma. Le pagine di destinazione sono nel panel HR. Nota: questo middleware del core usa le classi di supporto del modulo HR (`UnipaIdentity`, `AcademicYear`). È voluto: HR è il modulo dell'identità ed è sempre attivo.

### Dove mettere il codice

| Cosa | Dove |
|---|---|
| Entità usate da più moduli (User, Course, Auletta, ...) | `app/Models`, migration in `database/migrations`, schema `core` |
| Entità di un solo modulo | `Modules/<Modulo>/app/Models`, migration del modulo, schema del modulo |
| Enum condivisi | `app/Enums` (implementano `Filament\Support\Contracts\HasLabel` per le etichette italiane) |
| Impostazioni comuni della suite | `config/vivere.php` |
| Impostazioni di un modulo | `Modules/<Modulo>/config/config.php` |

### Creare un nuovo modulo

`php artisan module:make NomeModulo` (CamelCase, senza trattini). Gli stub in `stubs/nwidart-stubs`, abilitati in `config/modules.php`, non generano build frontend, controller, view né rotte Sanctum. Poi:
1. copiare `KaffettinoPanelProvider` adattando id, path, nome e il modulo in `discoverModuleComponents()`;
2. registrarlo nell'array `$providers` del `<Modulo>ServiceProvider`;
3. aggiungere la regola in `User::canAccessPanel()`;
4. se ha tabelle proprie: la prima migration crea lo schema e lo schema va aggiunto a `DB_SEARCH_PATH`;
5. aggiungere `Modules/<Modulo>/app/` ai `paths` di `phpstan.neon`.

### Dispositivi embedded e gestione fleet

- **Tabella unica:** tutti i dispositivi della suite stanno in **`core.devices`** (model `App\Models\Device`, tipo in `App\Enums\DeviceType`). Sono nel core perché li useranno più moduli: oggi gli ESP32 di Kaffettino, poi i kiosk di Calendario e i lettori del Magazzino.
- **Autenticazione senza Sanctum:**
  - ogni dispositivo ha un token di cui si salva solo l'hash SHA-256 (`Device::issueToken()`); il token in chiaro si mostra una sola volta;
  - un middleware della suite (da scrivere) verificherà `Authorization: Bearer <token>`;
  - gli id dei movimenti possono essere generati dal dispositivo (UUIDv7): così le ritrasmissioni non creano doppioni.
- **Codice della modalità amministratore** (cambio wifi sull'ESP32):
  - è diverso per ogni dispositivo e si cambia dal pannello (`Device::changeAdminCode()`);
  - nel DB è **cifrato e non hashato** (cast `encrypted`): quando il dispositivo è offline, proprio perché il wifi è cambiato, l'admin deve poterlo rileggere. Ogni lettura va nell'audit;
  - al dispositivo arriva solo un hash con sale (`adminCodeHashForDevice()`), con cui verifica il codice anche senza rete; sul tastierino va previsto un blocco dopo N tentativi sbagliati;
  - finché il dispositivo non ha scaricato il nuovo codice, `isAdminCodeSyncPending()` è vero.
- **Gestione fleet (da fare)** — Resource Filament "Dispositivi", per ora nel panel Kaffettino perché esistono solo i lettori Kaffettino:
  - elenco con stato online (`last_seen_at`), versione firmware, auletta, ultimo IP e codice "in attesa di sincronizzazione";
  - azioni sul singolo dispositivo: cambio di auletta, nuovo token (mostrato una volta), revoca;
  - visualizzare e cambiare il codice admin, sul singolo o **in blocco** sui dispositivi selezionati: azioni riservate agli admin e registrate nell'audit.
  - Quando arriveranno altri tipi di dispositivo, la fleet va spostata in un posto comune.

## Convenzioni database

- **Schemi**: `public` = tabelle tecniche di Laravel (migrations, cache, jobs, sessions, password_reset_tokens); `core` = entità condivise; `<modulo>` = tabelle di un solo modulo. Nel codice si scrive **sempre** il nome qualificato: `Schema::create('core.users')`, `#[Table('core.users')]`, `->constrained('core.users')`, `belongsToMany(X::class, 'core.admin_courses')`.
- `search_path` (`config/database.php`, `DB_SEARCH_PATH`) elenca tutti gli schemi: serve a `migrate:fresh`/`db:wipe` per vederli. Non va usato per evitare di qualificare i nomi.
- **Chiavi primarie UUID** (`$table->uuid('id')->primary()` + `HasUuids` nel model). FK con `foreignUuid(...)->constrained('schema.tabella')`, scritte in modo esplicito: le migration non devono dipendere dalle classi dei model.
- **Pivot pure** senza id, con chiave primaria composta (`$table->primary([...])`). Se la relazione ha uno storico (mandati, gestioni aulette) è un'entità con il suo id.
- Stringhe `text`, date `timestampTz`/`timestampsTz`, soldi in centesimi interi.
- **Enum**: colonna `enum()` di Laravel (varchar + CHECK). I valori si scrivono **a mano nella migration** con un commento che rimanda a `App\Enums\...`: una migration è una fotografia e non deve cambiare se cambia l'enum.
- **Indici con nomi espliciti** quando quello generato supererebbe i 63 caratteri (Postgres lo troncherebbe).
- Indici unique su espressioni o parziali (es. `lower(username)`, `WHERE removed_at IS NULL`) con `DB::statement`.
- Niente cancellazioni fisiche dove serve lo storico: `delisted_at`, `removed_at`, `revoked_at`, `anonymized_at`. FK `cascadeOnDelete` solo per i dati che appartengono strettamente al padre (profilo staff, preferenze, notifiche, pivot).
- **Ordine delle colonne** nelle migration e nel DBML: PK, dati, FK, timestamp (sezioni commentate `// ----- Primary keys -----` ecc.).
- Il DB e l'app lavorano in **UTC** (sessione Postgres `timezone=UTC`). Si mostra in `Europe/Rome` (`config('vivere.display_timezone')`, impostato anche su Filament). La logica "di calendario" (compleanni, inizio settimana, ottobre) va calcolata esplicitamente nel fuso italiano.

## Insidie note (già incontrate)

- **Regole di validazione**: `unique:core.users,email` è **sbagliato**, perché Laravel legge `core` come nome di connessione. Si scrive `Rule::unique(User::class, 'email')` (vale anche per `exists`).
- `Date::use(CarbonImmutable::class)`: i cast data restituiscono `CarbonImmutable`, quindi nei docblock `@property` si usa quel tipo (lo richiede PHPStan).
- Le notifiche stanno in `core.notifications` (model `App\Models\Notification`, relazione `User::notifications()` sovrascritta). La colonna `data` deve essere `jsonb`, perché Filament filtra con `data->format`.
- **Morph map** obbligatoria (`Relation::enforceMorphMap` in `AppServiceProvider`): ogni model usato in relazioni polimorfiche va aggiunto lì.
- `User` implementa `MustVerifyEmail`: senza, Filament non applica la verifica della mail.
- **2FA via email sempre attiva**: `User::hasEmailAuthentication()` restituisce `true` e non serve una colonna. I codici stanno in cache (Redis) e scadono dopo 4 minuti.
- **Cache e oggetti**: Laravel 13 di default non deserializza alcuna classe dalla cache (`cache.serializable_classes = false`). Filament salva in cache la scadenza dei codici 2FA come oggetto Carbon: con il default la data tornava come `__PHP_Incomplete_Class` e **ogni codice risultava non valido**. Per questo in `config/cache.php` sono ammesse solo le classi Carbon. Se un pacchetto mette in cache altri oggetti va aggiunto lì, con un motivo. Nei test la cache `array` non serializza: per riprodurre il comportamento di Redis serve `cache.stores.array.serialize = true` (vedi `tests/Feature/EmailMfaTest.php`).
- `role` e `status` dell'utente **non sono fillable**: si assegnano in modo esplicito solo in azioni autorizzate.
- La policy password (`Password::defaults`) vale in ogni ambiente; nei test si salta solo `uncompromised()`, che richiede la rete.
- I test girano su Postgres reale (database `testing`), non su SQLite: le migration usano schemi, jsonb e indici su espressioni.
- I test dei moduli stanno in `Modules/<Modulo>/tests/{Unit,Feature}` e girano insieme agli altri (`phpunit.xml` e `tests/Pest.php` includono `Modules/*/tests`).
- **Tabelle append-only nel DB**: `core.prevent_changes()` è una funzione trigger che rifiuta UPDATE e DELETE. È collegata a `kaffettino.transactions`, `transaction_items` e `stock_movements`. Un movimento sbagliato non si corregge: si registra un movimento `Adjustment`, che richiede sempre una nota.
- **Model con chiave non standard** (tabelle 1:1 o con chiave su un'altra entità, es. `kaffettino.user_settings`): si usa `#[Table(name: ..., key: 'user_id', keyType: 'string', incrementing: false)]`. Le factory dei moduli si collegano con `#[UseFactory(...)]`.
- **Pagine Filament con Select collegate a relazioni** (corso, scuola) su un form senza record, come la registrazione: serve `$schema->model(User::class)`, altrimenti errore `hasAttribute() on null`.
- Nei form, lo stato di un campo legato a un cast enum è l'**enum in modifica** e una **stringa in creazione**: usare `UserFields::courseYearFromState()`, o un helper analogo, invece di `CourseYear::tryFrom((string) ...)`.
- Gli `EventServiceProvider` dei moduli hanno `$shouldDiscoverEvents = false` e listener elencati in `$listen`: la discovery di Laravel guarda `app/Listeners` della root e rischierebbe di registrare due volte gli stessi listener.
- Test Livewire delle pagine Filament: prima `Filament::setCurrentPanel('hr')`. Lo staff dei test va creato con `User::factory()->staff()->withStaffProfile()`, altrimenti il middleware lo manda a completare il profilo.
- **`migrate:fresh` sul DB di sviluppo** ricrea anche il super admin: con `SUPERADMIN_PASSWORD` vuota la password cambia. Conviene impostarla nel `.env`; `php artisan db:seed --class=SuperAdminSeeder` la riapplica a un super admin già esistente. Per le verifiche usare i test (database `testing`), non il DB di sviluppo.

## Decisioni prese (8 ottobre 2026) — prevalgono sul PDF

Tecniche:
- **B1** PHP 8.5, Livewire ≥ 4.4.7, Filament 5.10 (Filament 4 richiede Livewire 3, quindi non è compatibile).
- **B2** ID **UUID**: Postgres li supporta nativamente; l'errore iniziale dipendeva da FK uuid verso `users.id` bigint.
- **B3** Schemi: `core` per le tabelle condivise, schema di modulo solo per le tabelle esclusive di quel modulo.
- Ruoli con l'enum `App\Enums\Role` (gerarchico) + Policy, senza spatie/permission. "Admin di corso" e "gestore auletta" sono relazioni (`core.admin_courses`, `core.auletta_managers`), non ruoli.
- Coordinate come `lat`/`lon` double nullable, senza PostGIS.
- Luoghi: `macroareas` → `departments` (dipartimenti accademici); `buildings` (edifici fisici) separati; `classrooms` → building (+ department facoltativo); `aulette` → building; `courses` → department e → auletta di afferenza (facoltativa).

Di dominio (risposte ai punti D del primo report):
- **D1** Lo username deve rispettare il formato `Nome.Cognome` (es. `MarioLuigi.Rossi03`) in registrazione, anche per le superiori. Dopo si può cambiare. È unico senza distinguere maiuscole.
- **D2** Se chi si registra somiglia a un utente bannato (`core.bans.email_pattern`): **warning**, l'account resta in attesa di revisione (Pending), mai rifiuto automatico.
- **D3** Le registrazioni le accetta lo **staff** (ruolo ≥ Staff), non gli studenti. La mail di avviso va agli admin del corso.
- **D4** Data di nascita completa (16 anni + caffè del compleanno).
- **D5** `phone_number` obbligatorio, giustificato da Oggetti Smarriti (contattare chi ha perso un oggetto). `gender` facoltativo, serve a declinare i testi M/F/neutro (`User::inflect()`).
- **D6** Il debito massimo è del **magazzino** (`kaffettino.warehouses.max_debt_cents`).
- **D7** L'avviso di scorta bassa va ai **gestori delle aulette** del magazzino.
- **D8** Solo lo **staff** può avere un conto Kaffettino (anche il panel è solo per lo staff attivo).
- **D9** PIN della card facoltativo, salvato come **hash**, si **reimposta** via mail e non si recupera.
- **D10** Relazioni e terminologia dei luoghi: vedi sopra.
- **D11** Filigrana va rifatta da zero per standardizzarla.
- **D12** Lo scraping dei professori è un **job schedulato**; il confronto si fa alla registrazione e produce un warning.
- **D13** Log di audit conservati **2 anni** (`vivere.audit_retention_days` = 730), confermato. Non c'è un massimo di legge (vale il principio di limitazione del GDPR); il minimo per i log di accesso degli amministratori di sistema è 6 mesi (Garante, provv. 27/11/2008). I 2 anni valgono per i log: i **movimenti** di Kaffettino sono dati di dominio e non si cancellano mai (potrebbero contare come scritture contabili, art. 2220 c.c.).

Palette (9 ottobre 2026):
- Colori del brand: blu **#071D99** (primario), viola #6200EE, giallo #FFCC33, sfondo #F3F3FD, testo #1D1D1D. I colori secondari si scelgono liberamente, purché stiano bene con questi.
- Le scale Filament stanno in `App\Support\VivereColors`, fatte a mano: Color::hex() di Filament avrebbe schiarito il blu dei pulsanti.
- Ogni colore del brand è esattamente nella sfumatura più usata: blu e viola a 600, giallo a 400, sfondo a gray-50, testo a gray-950. Colori di supporto: success = Emerald, danger = Rose, info = Sky.
- Il contrasto (WCAG AA) è verificato da `tests/Unit/VivereColorsTest.php`. Se si cambia una sfumatura, il test dice se si perde leggibilità.

Kaffettino (9 ottobre 2026):
- Fornitore e prezzo dipendono dal **magazzino** (`warehouse_products.supplier_id`).
- Le scorte **non scendono mai sotto zero**: CHECK nel DB, e la vendita viene rifiutata.
- Una sola **card attiva** per persona, valida per tutti i suoi conti.
- Ogni **coupon** si usa una sola volta per persona.
- Immagini "buongiornissimo" e festività **nel codice**, non nel DB, così nessuno le può smantellare dal pannello: enum PHP delle festività con le regole di data + cartella di immagini nel modulo.
- Gli admin possono **omaggiare gli ospiti**: l'omaggio finisce sul **conto dell'auletta**, cioè un conto intestato all'auletta invece che a una persona. È confermato:
  - va in negativo senza limite, non riceve le mail sui debiti e non ha card;
  - gli omaggi sono registrati al valore dei prodotti, così si sa quanto si è offerto;
  - il DB impone che ogni conto abbia esattamente un titolare.
- **Dispositivi**: in `core.devices`, con codice admin gestito dal pannello (vedi "Dispositivi embedded e gestione fleet").
- **Festività**: per ora sono quelle nazionali italiane, compreso San Francesco (4 ottobre, di nuovo festa dal 2026 per la L. 151/2025), più il **compleanno di Rosone (11 settembre)**. Sono in `Modules\Kaffettino\Enums\Holiday`, con la Pasqua calcolata senza dipendere dall'estensione `calendar`.
  - **TODO festività**: aggiungere le altre ricorrenze del documento e dell'associazione. Mancano: anniversario di Ingegneria e delle altre associazioni, Santa Lucia, Halloween, Morti, San Silvestro, Festa della donna, Pride, Black Friday, Domenica delle Palme, Festa della mamma, del papà e dei nonni, San Valentino, Giorno della memoria. Valutare anche Santa Rosalia (15 luglio, patrona di Palermo).

HR (9 ottobre 2026), proposte applicate da confermare:
- **Passaggio d'anno** (`CourseYear::next()`, job `hr:advance-academic-year` il 1° ottobre alle 3):
  - si avanza di un anno; T3 → TFC e M2 → MFC; fuori corso e laureandi restano dove sono;
  - i part-time avanzano di un semestre (PTT-3-2 → TFC, PTM-2-2 → MFC);
  - le superiori restano invariate e passano a "da confermare": le conferma un admin;
  - per tutto ottobre ognuno conferma o corregge il proprio anno al login.
- **Ruoli**:
  - nessuno assegna SuperAdmin dall'interfaccia (lo crea il seeder) e nessuno può modificare, bannare o declassare un super admin;
  - un admin non cambia il proprio ruolo;
  - anche il super admin deve completare il profilo staff (è "almeno admin").
- **Ban** (solo admin, mai sé stessi né un super admin):
  - la motivazione arriva per mail;
  - c'è anche "Revoca ban" (non è nel documento, ma serve per correggere errori); lo storico resta.
- **Chi vede cosa**: lo staff vede gli utenti e accetta le registrazioni, ma non vede telefono e profilo staff (codice fiscale, ecc.), riservati agli admin; conferma dell'anno "da confermare" solo per gli admin.
- **Gestori auletta**: alla rimozione parte la mail per restituire le chiavi solo se le chiavi erano già state consegnate.
- **Inserimento manuale**:
  - l'admin crea l'account già attivo, senza password; l'utente riceve un link per sceglierla, che vale anche come verifica della mail;
  - TODO: far accettare l'informativa privacy al primo accesso a chi è stato inserito dallo staff.
- **Disiscrizione**:
  - dal profilo, scrivendo ELIMINA; i dati personali vengono anonimizzati e la riga resta, così lo storico non si rompe;
  - si cancellano profilo staff, preferenze e notifiche;
  - restano lo storico dei ban (per riconoscere chi si ri-registra) e i movimenti di Kaffettino.
- **Accettazione in blocco**: accetta solo le registrazioni senza avvisi; quelle con avvisi vanno viste una per una.

Storage (9 ottobre 2026):
- **Disco locale** privato di Laravel con URL firmati temporanei, più backup. MinIO è stato tolto da `compose.yaml` (community edition archiviata a febbraio 2026). Uno storage S3 (Garage o SeaweedFS) solo se in futuro servirà più di un server.

## Stato attuale

Fatto:
- schema `core` completo (22 tabelle, compresa `core.devices`) con migration, model, relazioni, factory e seeder (cariche istituzionali, super admin da `.env`, dati finti in locale);
- schema `kaffettino` completo (16 tabelle) con migration, model e factory. I vincoli (scorte ≥ 0, una card attiva, un titolare per conto, movimenti append-only, segno degli importi, un regalo di compleanno all'anno, coupon una volta per persona) sono garantiti dal DB e coperti da test;
- palette ufficiale nel tema, scheda `mail` in `composer dev`, enum delle festività;
- **piattaforma HR**, cioè tutta la parte HR del documento:
  - account: registrazione completa, schermata di attesa, login e reset password con username o email (mail oscurata), 2FA via email, profilo con preferenze email, cambio mail verificato e disiscrizione con anonimizzazione;
  - percorso dell'account: profilo staff obbligatorio e cambio a mail UNIPA quando si lascia la scuola superiore;
  - gestione utenti: tab per stato, avvisi su somiglianza con ban, professori e nome/mail, accettazione (anche in blocco), ban/revoca con mail, cambio ruolo, inserimento manuale con mail per la password;
  - assegnazioni: mandati istituzionali con storico, admin di corso (dashboard con i corsi scoperti), gestori auletta con mail per le chiavi;
  - dati dell'ateneo: macroaree, dipartimenti, edifici, aule, corsi, scuole, cariche;
  - comunicazioni: notifiche broadcast con filtri sui destinatari, changelog con popup al primo accesso in ogni piattaforma;
  - **audit log** (DB append-only + file, 2 anni, consultabile solo dai super admin);
  - job: passaggio d'anno e conferma di ottobre, sincronizzazione o import dei professori;
  - traduzioni italiane di Laravel (`lang/it`);
- panel Kaffettino (solo dashboard), su `VivereSuitePanelProvider` come HR;
- comando di avvio unico, CI con Postgres, stub per i moduli futuri;
- test Pest su schema, relazioni, vincoli e accesso ai panel.

HR, cosa manca:
1. **scraper dei professori** (D12).
   - **Oggi:** la fonte è `NoProfessorSource`, che non restituisce nomi. La pagina `www.unipa.it/persone/docenti/` non contiene l'elenco nell'HTML (probabilmente lo carica via JavaScript o tramite una ricerca), quindi non c'è ancora una fonte da cui leggere.
   - **Nel frattempo:** l'elenco si carica da file, un nome per riga, con `php artisan hr:import-professors file.txt`.
   - **Da fare:**
     - trovare con l'associazione una pagina pubblica o un export (anche periodico) con l'elenco dei docenti;
     - scrivere una classe che implementa `Modules\HumanResources\Contracts\ProfessorSource` e collegarla in `HumanResourcesServiceProvider::register()`.
   - **Come deve comportarsi:**
     - se la pagina cambia formato deve lanciare un'eccezione, non restituire un elenco vuoto;
     - il job settimanale `hr:sync-professors` è già pronto: è isolato, scrive nel log e avvisa i super admin se fallisce;
     - `symfony/dom-crawler` si installa solo in quel momento.
2. **allegati delle notifiche** (`core.notification_attachments`): si aggiungono insieme alla pipeline di caricamento file con antivirus (vedi "Decisioni ancora aperte"), sia nell'invio broadcast (`SendNotification`) sia nelle notifiche dei moduli.
3. **accettazione dell'informativa privacy**.
   - **Utenti inseriti dallo staff:** oggi non la accettano mai. `CreateUser` imposta `privacy_accepted_at` a nome loro, ed è un TODO nel codice.
   - **Tutti gli utenti:** quando cambia `VIVERE_TERMS_VERSION` va chiesto di accettare la nuova versione.
   - **Da fare:** un passo in più in `EnsureAccountIsReady`: se `terms_version` è diversa da `config('vivere.terms_version')`, si va a una pagina HR "Accetta l'informativa", che aggiorna `privacy_accepted_at` e `terms_version`. Gli utenti inseriti dallo staff vanno creati con `terms_version` vuota o "da accettare", così il passo scatta al primo accesso.
4. **link mancanti**: servono gli indirizzi definitivi di
   - **informativa privacy** (`VIVERE_PRIVACY_POLICY_URL`), linkata nella registrazione;
   - **pagina dei suggerimenti** (`VIVERE_SUGGESTIONS_URL`), linkata nel menu utente di ogni piattaforma.

   Finché sono vuoti, il link all'informativa resta testo semplice e la voce "Suggerimenti" non compare.
5. extra del documento: pubblicare i rappresentanti (CCS) per il sito web;
6. **controllo del codice fiscale** (`core.staff_profiles.tax_code`).
   - **Oggi:** si controllano solo i 16 caratteri. In `CompleteStaffProfile` anche che siano alfanumerici (`/^[A-Za-z0-9]{16}$/`), in `UserResource` neanche quello. Si salva in maiuscolo.
   - **Da fare:** una regola di validazione condivisa (es. `Modules\HumanResources\Rules\ItalianTaxCode`), usata in tutti e due i form. Basta l'algoritmo ufficiale, senza librerie.
   - **Cosa deve controllare:**
     - **formato:** 6 lettere (cognome e nome), 2 cifre (anno), una lettera del mese tra `ABCDEHLMPRST`, 2 cifre (giorno, +40 per le donne), il codice catastale del comune (una lettera e 3 cifre; `Z` + 3 cifre per i nati all'estero), il carattere di controllo;
     - **omocodia:** le cifre possono essere sostituite dalle lettere `LMNPQRSTUV`, quindi la regex non deve rifiutare questi casi;
     - **carattere di controllo:** calcolato con le tabelle ufficiali dei caratteri in posizione pari e dispari; è il controllo che intercetta la maggior parte degli errori di battitura.
   - **Coerenza con i dati dell'utente:** come **avviso** per gli admin, non come errore, perché omocodie e casi particolari non vanno bloccati. Controllare che data di nascita, cognome e nome corrispondano a `birthday`, `surname` e `name`. Il sesso del codice (giorno +40) non va confrontato con `gender`: è facoltativo e ha anche il valore "Altro".
   - **Test:** codici validi, carattere di controllo sbagliato, omocodia, nati all'estero, mese non valido.
7. **immagine del profilo** (foto o avatar dell'utente), facoltativa (GDPR: dato inserito volontariamente).
   - **Dati:** colonna `core.users.avatar_path` (text, null), da aggiungere con una nuova migration e nel DBML.
   - **Caricamento:** dal profilo (`EditProfile`) con il `FileUpload` di Filament (`->avatar()->image()->imageEditor()` per ritagliare). Formati JPG, PNG e WebP, al massimo 2 MB, ridimensionata (es. 512×512). Passa dalla pipeline di upload: antivirus, ricodifica dell'immagine che toglie i metadati EXIF/GPS.
   - **Dove sta:** disco locale privato, servita solo agli utenti loggati (URL firmato temporaneo o rotta autenticata), mai pubblica.
   - **Filament:** `User` implementa `Filament\Models\Contracts\HasAvatar` (`getFilamentAvatarUrl()`): così compare nel menu utente, nelle tabelle e nelle notifiche.
   - **Gestione:**
     - l'utente può toglierla quando vuole;
     - gli admin possono rimuovere un'immagine inappropriata (registrato nell'audit; per i casi gravi c'è il ban);
     - `AnonymizeUser` deve cancellare anche il file.
   - **Test:** caricamento, formato rifiutato, rimozione, cancellazione all'anonimizzazione, visibilità solo agli utenti loggati.
8. **avatar di default senza servizi esterni (GDPR, da sistemare presto).** Oggi Filament usa `UiAvatarsProvider`, che genera l'avatar con le iniziali chiamando `https://ui-avatars.com/api/?name=Nome+Cognome`. Quindi, a ogni pagina, il browser invia nome, cognome e indirizzo IP dell'utente a un servizio esterno, senza consenso.
   - **Da fare:** un provider locale (es. `App\Filament\AvatarProviders\InitialsAvatarProvider`), che genera un SVG con le iniziali e i colori della palette come data URI, senza librerie. Va impostato in `VivereSuitePanelProvider` con `->defaultAvatarProvider(...)` e vale anche quando l'utente non ha caricato un'immagine (punto 7).
   - **Da valutare insieme:** anche il font di Filament viene caricato da un servizio esterno (`fonts.bunny.net`, che dichiara di non registrare gli IP). Per non avere richieste a terzi si può ospitare il font in locale (`->font(..., provider: LocalFontProvider::class)` con i file in `public/`).

Prossimi passi Kaffettino (in ordine indicativo):
1. azioni di dominio, scritte una volta e usate sia da Filament sia dall'API:
   - acquisto: lock del conto, controllo debito massimo e scorte, coupon, regalo di compleanno;
   - ricarica: mai sul proprio conto;
   - omaggio sul conto dell'auletta, rettifica;
2. middleware del token di dispositivo e API per l'ESP32 (prodotti del magazzino, acquisto, sincronizzazione della configurazione);
3. gestione fleet (vedi sopra) e Resource per magazzini, prodotti, fornitori, card, conti, coupon;
4. home con l'immagine "buongiornissimo" (priorità: compleanno, debito, festività, giorno della settimana; immagini nella cartella del modulo, una sottocartella per ogni `Holiday`);
5. resoconti (filtri per giorno, settimana, mese, anno, auletta e utente; visibilità per gli admin dei corsi afferenti all'auletta; export Excel) e statistiche;
6. job: mail settimanale ai debitori, avviso di scorte basse ai gestori, auguri di compleanno;
7. reset del PIN via mail con link firmato.

## Decisioni ancora aperte

- **Pipeline di caricamento file** (proposta: da implementare con il primo modulo che carica file, cioè Drive, Segnalazioni, Oggetti Smarriti o gli allegati delle notifiche). Ogni file passa da una procedura unica nel core:
  1. formati ammessi decisi per modulo, con lista bianca e non nera, controllando sia l'estensione sia il tipo reale del file (magic bytes);
  2. limite di dimensione;
  3. impronta SHA-256 per scartare i doppioni;
  4. quarantena: il file non è scaricabile finché un job in coda non l'ha controllato con l'antivirus **ClamAV** (container `clamav/clamav`, firme aggiornate in automatico, ~1-1,5 GB di RAM). Client clamd scritto nella suite: il protocollo INSTREAM è di poche righe;
  5. pulizia: i PDF riscritti (la filigrana con FPDI già lo fa ed elimina JavaScript e file incorporati), le immagini ricodificate togliendo i metadati EXIF/GPS (GDPR);
  6. se è tutto a posto il file viene pubblicato (per il Drive dopo l'approvazione dello staff), altrimenti eliminato con notifica a chi l'ha caricato e voce di audit.
- **Logo di Vivere** per il tema (la palette è decisa).
- **URL della pagina suggerimenti** (`VIVERE_SUGGESTIONS_URL`).
- **Festività** dell'associazione e del documento: vedi il TODO nella sezione Kaffettino.
- **Kaffettino**: importi in centesimi interi con aritmetica intera, senza brick/money (proposta).
- Regole di avanzamento dell'anno di corso (T3→?, M2→?, part-time).
