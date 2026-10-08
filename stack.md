# Suite Vivere — Stack tecnologico e dipendenze

> Documento di riferimento per lo stack e le dipendenze di ogni modulo.
> Ultimo aggiornamento: ottobre 2026. Le versioni indicate sono quelle installate (vedi `composer.lock` e `package-lock.json`).
> Regola generale: **meno librerie possibile**. Si aggiunge un pacchetto solo se è ben documentato, mantenuto e accorcia davvero il codice.
> Ogni pacchetto si installa **quando si sviluppa il modulo che lo usa**, non in anticipo.

---

## 1. Stack finale

| Livello | Scelta |
|---|---|
| Linguaggio | PHP 8.5 (backend), Python 3.12 (script di supporto: scraping, watermark) |
| Framework | Laravel 13, monolite modulare |
| Modularizzazione | `nwidart/laravel-modules` 13 — un modulo per ogni progetto della suite |
| Database | PostgreSQL 18 — un'istanza e un database; schemi `public` (tabelle tecniche Laravel), `core` (entità condivise), uno schema per ogni modulo con tabelle proprie (`kaffettino`, ...) |
| Chiavi primarie | UUID (v7, generati da Laravel con `HasUuids`) |
| Cache / Code / Sessioni | Redis |
| UI | **Filament 5 per tutti i moduli** ("Filament-first"), su Livewire 4 + Alpine.js + Tailwind CSS 4 |
| Real-time | Laravel Reverb + Laravel Echo (quando servirà: Assistest, Calendario) |
| Storage file | Disco locale privato di Laravel con URL firmati temporanei (MinIO community è archiviato, vedi sezione 6) |
| Notifiche mobile | PWA + Web Push (nessuna app nativa) |
| Build frontend | Vite, **un solo build** nella root (i moduli non hanno un proprio `vite.config.js`) |
| Deploy | Docker Compose |

### Servizi Docker Compose

| Servizio | Immagine / Note |
|---|---|
| `pgsql` | `postgres:18-alpine` |
| `redis` | `redis:alpine` |
| `mailpit` | Solo in dev — cattura tutte le email in uscita (http://localhost:8025) |
| `clamav` | *Previsto*: antivirus per i file caricati (`clamav/clamav`), da aggiungere con il primo modulo che carica file |
| `laravel.test` | Container PHP di Sail: non usato nello sviluppo "ibrido" (PHP gira sull'host) |

In produzione serviranno anche: `app` (PHP-FPM 8.5 + `pdo_pgsql`, `redis`, `gd`, `zip`, `intl`, LibreOffice headless per Filigrana), `nginx`, worker delle code, scheduler (`php artisan schedule:run`) ed eventualmente `reverb`.

---

## 2. Progetto monolitico principale (core condiviso)

### Composer — installati

| Pacchetto | A cosa serve |
|---|---|
| `laravel/framework` ^13 | Framework |
| `nwidart/laravel-modules` ^13 | Struttura a moduli (HumanResources, Kaffettino, ...) |
| `wikimedia/composer-merge-plugin` | Unisce i `composer.json` dei moduli (autoload) |
| `filament/filament` ^5.10 | UI di tutti i moduli: panel, CRUD, tabelle, form, dashboard, notifiche, grafici (Chart.js), export XLSX/CSV, import CSV, autenticazione con 2FA via email |
| `livewire/livewire` ^4.4.7 | Base di Filament e dei componenti personalizzati |
| `spatie/laravel-activitylog` ^5.1 | Audit log append-only (`core.activity_log`, model `App\Models\Activity`), con copia su file (canale `audit`) |
| `laravel/tinker` | Console interattiva |

### Composer — previsti (da installare quando servono)

| Pacchetto | Modulo | A cosa serve |
|---|---|---|
| `laravel/reverb` | Assistest, Calendario | Server WebSocket |
| `laravel/horizon` | Produzione | Monitoraggio code Redis (valutare se serve davvero) |
| `laravel-notification-channels/webpush` | Core | Web Push per le notifiche PWA |
| `symfony/dom-crawler` + `symfony/css-selector` | HR, Orari, Aule Libere | Scraping (professori, orari, aule UNIPA) con il client `Http` di Laravel |
| `spatie/laravel-medialibrary` | Segnalazioni, Oggetti Smarriti, Magazzino | Allegati e media (valutare caso per caso, per allegati semplici basta lo Storage di Laravel) |

### NPM

| Pacchetto | A cosa serve |
|---|---|
| `tailwindcss` + `@tailwindcss/vite` | Styling (tema unico `resources/css/filament/theme.css`) |
| `vite` + `laravel-vite-plugin` | Build |
| `@laravel/multiplex` (dev) | Usato da `php artisan dev` su Linux/macOS per avviare tutti i processi in un terminale |
| `concurrently` | Usato da `php artisan dev` su Windows |
| `laravel-echo` + `pusher-js` | *Previsti*: client WebSocket verso Reverb |
| `vite-plugin-pwa` (o service worker manuale) | *Previsto*: manifest PWA + service worker |

Alpine.js è già incluso in Livewire, non va installato a parte.

### Dev / Qualità (require-dev)

| Pacchetto | A cosa serve |
|---|---|
| `pestphp/pest` + `pestphp/pest-plugin-laravel` | Test |
| `laravel/pint` | Code style |
| `larastan/larastan` | Analisi statica (livello 7) |
| `laravel/pail` | Log in tempo reale (processo `logs` di `artisan dev`) |
| `laravel/sail` | Fornisce `compose.yaml` e le immagini Docker di sviluppo |
| `laravel/pao` | Output dei test ottimizzato per gli agenti LLM |
| `fakerphp/faker`, `mockery/mockery`, `nunomaduro/collision` | Supporto ai test |

### Rimossi o scartati (e perché)

| Pacchetto | Motivo |
|---|---|
| `laravel/fortify` | La sua 2FA è solo TOTP; Filament 5 fa già login, reset password, verifica email, rate limiting e 2FA via email |
| `spatie/laravel-permission` | 4 ruoli fissi e gerarchici + relazioni di contesto (admin di corso, gestore auletta): bastano l'enum `App\Enums\Role` e le Policy |
| `laravel/sanctum` | Gli ESP32 usano un token di dispositivo verificato da un middleware della suite (tabella `kaffettino.devices`) |
| `coolsam/modules` | Ogni modulo registra il proprio panel Filament in poche righe (vedi `VivereSuitePanelProvider`) |
| `predis/predis` | Si usa l'estensione `phpredis`, già presente |
| `apexcharts` | Bastano i widget grafici di Filament (Chart.js) |
| `maatwebsite/excel` | Filament esporta già in XLSX/CSV e importa CSV |
| `brick/money` | Proposta: importi in centesimi interi e calcolo degli sconti con aritmetica intera (da confermare con Kaffettino) |
| `livewire/blaze` | Residuo dello starter kit, non usato |

---

## 3. Dipendenze per modulo

Molti moduli **non richiedono nulla oltre al core**: sono CRUD + Policy + Notifications. Dove serve altro, è indicato sotto.

### 3.1 HR (core identità) — fatto (vedi CLAUDE.md, "Stato attuale")

| Dipendenza | Note |
|---|---|
| Filament (core) | Login, reset password, verifica email, 2FA via email obbligatoria, rate limiting del login |
| Validazione password nativa | `Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()` in `AppServiceProvider` |
| Enum `Role` + Policy | Ruoli, admin per corso (`core.admin_courses`), ruoli istituzionali con storico |
| `symfony/dom-crawler` (previsto) | Scraping professori ateneo: si installa quando si trova la fonte (oggi c'è l'import da file) |
| `spatie/laravel-activitylog` (core) | Audit di ban, cambi di ruolo, accettazioni, notifiche, ... |
| — | Schermata attesa, matching ban, migrazione email: logica applicativa, nessun pacchetto |

### 3.2 Problemi Tecnici

| Dipendenza | Note |
|---|---|
| Storage Laravel | Foto/video allegati alla segnalazione |
| — | Versione ticketing (extra): riusa Notifications + Filament, nessun pacchetto nuovo |

### 3.3 Kaffettino — in sviluppo (schema DB fatto)

| Dipendenza | Note |
|---|---|
| Middleware "token di dispositivo" | API REST per l'ESP32, senza Sanctum (dispositivi in `core.devices`, gestione fleet nel pannello) |
| Filament (core) | Resoconti, export Excel, statistiche e grafici |
| — | Soldi in centesimi interi, mai float (debiti, saldi, ricariche) |

**Firmware embedded (ESP32, fuori dal monolite):** PlatformIO + Arduino framework; librerie tipiche: `Adafruit PN532` (NFC), `U8g2` (OLED), `DFRobotDFPlayerMini` (MP3), `Keypad`, `ArduinoJson`, `WiFiClientSecure`. Parla solo con l'API del modulo.

### 3.4 Magazzino

| Dipendenza | Note |
|---|---|
| `picqer/php-barcode-generator` | Generazione codici a barre per gli item |
| Storage Laravel / `spatie/laravel-medialibrary` | Foto item (per versione) e documenti ordine (scontrini, fatture) |
| Filament (core) | Report ed export |
| — | Scanner barcode (extra Raspberry): la pistola USB emula una tastiera, basta un input — nessuna dipendenza |

### 3.5 Assistest

| Dipendenza | Note |
|---|---|
| `laravel/reverb` + `laravel-echo` | Lobby real-time stile Kahoot, riconnessione con score |
| Alpine.js (core) | Timer countdown e interazioni di gioco client-side |
| — | Confronto risposte numeriche con tolleranza: aritmetica PHP, valutare `brick/math` solo se serve precisione arbitraria |

### 3.6 Drive

| Dipendenza | Note |
|---|---|
| Storage locale + pipeline di upload (sezione 6) | File con download tramite URL firmati temporanei, antivirus ClamAV |
| `setasign/fpdi` + `tecnickcom/tcpdf` | Validazione PDF e filigrana in post-upload (condiviso col modulo Filigrana) |
| Postgres full-text (`tsvector`) | Barra di ricerca — parti da qui; `laravel/scout` + Meilisearch solo se in futuro non basta |
| — | Versionamento "stile git": tabella `versioni`, nessun pacchetto |

### 3.7 Eventi

| Dipendenza | Note |
|---|---|
| `spatie/icalendar-generator` | Inviti .ics compatibili Google/Outlook |
| Filament (core) | Allegati evento, export risposte sondaggi |

### 3.8 Orientamento

Nessuna dipendenza extra: CRUD scuole/responsabili + Notifications + generazione mail (Blade). Integrazione Calendario via `spatie/icalendar-generator`.

### 3.9 Orari

| Dipendenza | Note |
|---|---|
| `symfony/dom-crawler` | Scraping orari dal sito UNIPA |
| `spatie/browsershot` + Chromium headless | Rendering layout+palette → immagine PNG dell'orario (layout come template Blade/HTML) |
| `spatie/icalendar-generator` | Export orario verso calendario personale |

*Nota: Browsershot richiede Node + Puppeteer/Chromium nel container `app`.*

### 3.10 Aule Libere

| Dipendenza | Note |
|---|---|
| `leaflet` (npm) | Mappa posizione aula |
| `symfony/dom-crawler` | Import iniziale aule + wrapper del portale UNIPA |

### 3.11 QR

| Dipendenza | Note |
|---|---|
| `qr-code-styling` (npm) | Generazione client-side con colore, forma dei punti, logo interno, export SVG/PNG/JPEG — copre tutti i requisiti |
| `endroid/qr-code` | Alternativa server-side se preferisci generare in PHP |

### 3.12 Calendario (modulo non ancora creato)

| Dipendenza | Note |
|---|---|
| `spatie/icalendar-generator` | Inviti .ics via mail |
| Reverb/Echo | Sync in tempo reale della dashboard embedded |

**Embedded (Raspberry Pi):** nessun firmware — è un browser in modalità kiosk (Chromium `--kiosk`) che punta a una pagina aggiornata via Echo.

### 3.13 Oggetti Smarriti

| Dipendenza | Note |
|---|---|
| Storage Laravel | Foto oggetto |
| — | Integrazione Orari, copy annunci, mail mirate per corso: logica applicativa |
| `leaflet` (npm, extra) | Mappa università se si implementa l'extra |

### 3.14 Elezioni (modulo non ancora creato)

| Dipendenza | Note |
|---|---|
| Filament (core) | Import liste persone da verificare (CSV), export risultati |
| — | Metodi di calcolo (D'Hondt, proporzionale...): classi PHP pure, ben testate con Pest — è il cuore del modulo, meglio non dipendere da librerie |

*Attenzione GDPR (dal preambolo): non memorizzare opinioni politiche — solo conteggi aggregati, mai il voto associato alla persona.*

### 3.15 Filigrana (da rifare per standardizzarla)

| Dipendenza | Note |
|---|---|
| LibreOffice headless (`soffice --headless --convert-to pdf`) | Conversione .docx → .pdf, invocata via `Process` — nel container `app` |
| `setasign/fpdi` + `tecnickcom/tcpdf` | Applicazione filigrana (logo o testo, opacità ≥ 5%) |
| `ZipArchive` (nativo PHP) | Zip in caso di file multipli |
| Filament (core) | Upload multiplo con drag and drop |

### 3.16 Segnalazioni

| Dipendenza | Note |
|---|---|
| Storage Laravel | Foto/video della problematica |
| `spatie/laravel-activitylog` | Changelog append-only per segnalazione |
| — | Rilevamento duplicati: full-text Postgres su titolo/descrizione; mail precompilate: `mailto:` generato o Blade |

---

## 4. Riepilogo installazione

```bash
composer setup   # prima installazione completa (vedi coseUtili.md)
composer dev     # avvio quotidiano: servizi Docker + server, code, scheduler, log, Vite
```

## 5. Principi da mantenere

1. **Un solo `User`, schema `core`**: ogni modulo referenzia `core.users` con FK reali.
2. **Logica di dominio in classi Action/Service**: le pagine Filament e i controller API (embedded) chiamano lo stesso codice.
3. **Soldi sempre in centesimi interi**, mai float.
4. **Niente cancellazioni fisiche** dove i documenti chiedono storicità (prodotti Kaffettino, oggetti smarriti, ruoli istituzionali, versioni Drive): flag/timestamp (`delisted_at`, `removed_at`, ...).
5. **Audit append-only**: log su DB + canale Monolog dedicato su file per i requisiti "in OS", accesso solo super admin, conservazione 2 anni.
6. **Ogni scraping è un job isolato e schedulato**: se fallisce, non blocca nulla e notifica gli admin.
7. **UI standard**: ogni modulo è un panel Filament che estende `App\Providers\Filament\VivereSuitePanelProvider`.

## 6. Storage dei file

- **Deciso**: disco locale privato di Laravel con URL firmati temporanei, più backup. MinIO è stato tolto da `compose.yaml`: la community edition non distribuisce più immagini Docker da ottobre 2025 ed è archiviata da febbraio 2026. Se un giorno servirà uno storage S3 (più server), Garage o SeaweedFS: con Flysystem basta cambiare la configurazione. Con il disco locale non serve `league/flysystem-aws-s3-v3`.
- **Previsto**: ogni upload passa da una pipeline unica (dettagli in CLAUDE.md):
  - lista bianca dei formati per modulo (estensione + tipo reale);
  - limite di dimensione e SHA-256 contro i doppioni;
  - quarantena con antivirus **ClamAV** (servizio Docker `clamav/clamav`, client clamd scritto nella suite, nessuna libreria);
  - riscrittura dei PDF e rimozione dei metadati EXIF/GPS dalle immagini.
