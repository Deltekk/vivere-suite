# Suite Vivere — Stack tecnologico e dipendenze

> Documento di riferimento per lo stack e le dipendenze di ogni modulo.
> Ultimo aggiornamento: luglio 2026 — verificare le versioni al momento dell'installazione.

---

## 1. Stack finale

| Livello | Scelta |
|---|---|
| Linguaggio | PHP 8.3+ (backend), Python 3.12 (script di supporto: scraping, watermark) |
| Framework | Laravel 12, monolite modulare |
| Modularizzazione | `nwidart/laravel-modules` — un modulo per ogni progetto della suite |
| Database | PostgreSQL 16 — un'istanza, uno schema per modulo (`core`, `drive`, `kaffettino`, ...) |
| Cache / Code / Sessioni | Redis 7 |
| Frontend | TALL stack: Tailwind CSS 4 + Alpine.js 3 + Laravel Livewire 3 |
| Pannelli admin | Filament 4 |
| Real-time | Laravel Reverb (WebSocket self-hosted) + Laravel Echo |
| Storage file | MinIO (S3-compatible) via Flysystem |
| Notifiche mobile | PWA + Web Push (nessuna app nativa) |
| Build frontend | Vite (integrato in Laravel) |
| Deploy | Docker Compose |

### Servizi Docker Compose

| Servizio | Immagine / Note |
|---|---|
| `app` | PHP-FPM 8.3 + estensioni (`pdo_pgsql`, `redis`, `gd`, `zip`, `intl`) + **LibreOffice headless** (per Filigrana) |
| `nginx` | Reverse proxy |
| `postgres` | `postgres:16` |
| `redis` | `redis:7` |
| `horizon` | Stesso container `app`, comando `php artisan horizon` (worker code) |
| `scheduler` | Stesso container `app`, cron con `php artisan schedule:run` |
| `reverb` | Stesso container `app`, comando `php artisan reverb:start` |
| `minio` | `minio/minio` |
| `mailpit` | Solo in dev — cattura tutte le email in uscita |

---

## 2. Progetto monolitico principale (core condiviso)

Dipendenze installate una volta, usate da tutti i moduli.

### Composer

| Pacchetto | A cosa serve |
|---|---|
| `nwidart/laravel-modules` | Struttura a moduli (HR, Drive, Kaffettino...) |
| `livewire/livewire` | Componenti UI reattivi in PHP/Blade |
| `filament/filament` | Pannelli admin (CRUD, tabelle, dashboard) |
| `coolsam/modules` (o plugin equivalente) | Integrazione Filament ↔ laravel-modules |
| `laravel/fortify` | Backend auth headless: login, reset password, 2FA — la UI la fai tu in Livewire (necessario per i flussi custom HR) |
| `laravel/sanctum` | Token API per i dispositivi embedded (ESP32, Raspberry) |
| `laravel/reverb` | Server WebSocket (Assistest live, aggiornamenti real-time) |
| `laravel/horizon` | Gestione e monitoraggio code Redis |
| `spatie/laravel-permission` | Ruoli e permessi (studente / staff / admin / super admin + gestore auletta) |
| `spatie/laravel-medialibrary` | Allegati e media (foto segnalazioni, oggetti smarriti, allegati notifiche/eventi) |
| `laravel-notification-channels/webpush` | Canale Web Push per le Notifications |
| `league/flysystem-aws-s3-v3` | Driver S3 per MinIO |
| `maatwebsite/excel` | Export Excel (resoconti Kaffettino, Eventi, Elezioni) |
| `spatie/laravel-activitylog` | Audit log applicativo append-only (requisito super admin) — in aggiunta, canale Monolog dedicato su file per i log "in OS" richiesti dal documento |
| `symfony/dom-crawler` + `symfony/css-selector` | Scraping (professori, orari, aule UNIPA) con il client `Http` di Laravel |
| `predis/predis` (o est. `phpredis`) | Client Redis |

### NPM

| Pacchetto | A cosa serve |
|---|---|
| `tailwindcss` | Styling |
| `alpinejs` | Interazioni client-side (già incluso in Livewire, esplicito se serve standalone) |
| `laravel-echo` + `pusher-js` | Client WebSocket verso Reverb |
| `vite-plugin-pwa` (o service worker manuale) | Manifest PWA + service worker per Web Push |
| `apexcharts` | Grafici e statistiche (Kaffettino, Assistest, Segnalazioni) |

### Dev / Qualità (require-dev)

| Pacchetto | A cosa serve |
|---|---|
| `pestphp/pest` | Test |
| `laravel/pint` | Code style |
| `larastan/larastan` | Analisi statica |
| `laravel/telescope` | Debug in locale |
| `barryvdh/laravel-debugbar` | Debug in locale |

---

## 3. Dipendenze per modulo

Molti moduli **non richiedono nulla oltre al core**: sono CRUD + Policy + Notifications. Dove serve altro, è indicato sotto.

### 3.1 HR (core identità)

| Dipendenza | Note |
|---|---|
| `laravel/fortify` (core) | Login, reset password, 2FA via email |
| Validazione password nativa | `Password::min(12)->mixedCase()->numbers()->symbols()->uncompromised()` — copre policy + password comuni/compromesse |
| `RateLimiter` nativo | Rate limiting login |
| `spatie/laravel-permission` (core) | Ruoli, admin per corso, ruoli istituzionali con storico |
| `symfony/dom-crawler` (core) | Scraping professori ateneo (job schedulato isolato) |
| — | Schermata attesa, matching ban, migrazione email: logica applicativa, nessun pacchetto |

### 3.2 Problemi Tecnici

| Dipendenza | Note |
|---|---|
| `spatie/laravel-medialibrary` (core) | Foto/video allegati alla segnalazione |
| — | Versione ticketing (extra): riusa Notifications + Livewire, nessun pacchetto nuovo |

### 3.3 Kaffettino

| Dipendenza | Note |
|---|---|
| `laravel/sanctum` (core) | API REST per l'embedded ESP32 (token per dispositivo) |
| `maatwebsite/excel` (core) | Export resoconti |
| `apexcharts` (core, npm) | Statistiche, trend, istogrammi |
| `brick/money` | **Consigliato**: gestione importi in centesimi — mai float per i soldi (debiti, saldi, ricariche) |

**Firmware embedded (ESP32, fuori dal monolite):** PlatformIO + Arduino framework; librerie tipiche: `Adafruit PN532` (NFC), `U8g2` (OLED), `DFRobotDFPlayerMini` (MP3), `Keypad`, `ArduinoJson`, `WiFiClientSecure`. Parla solo con l'API Sanctum del modulo.

### 3.4 Magazzino

| Dipendenza | Note |
|---|---|
| `picqer/php-barcode-generator` | Generazione codici a barre per gli item |
| `spatie/laravel-medialibrary` (core) | Foto item (per versione) e documenti ordine (scontrini, fatture) |
| `maatwebsite/excel` (core) | Report |
| — | Scanner barcode (extra Raspberry): la pistola USB emula una tastiera, basta un input Livewire — nessuna dipendenza |

### 3.5 Assistest

| Dipendenza | Note |
|---|---|
| `laravel/reverb` + `laravel-echo` (core) | Lobby real-time stile Kahoot, riconnessione con score |
| Alpine.js (core) | Timer countdown e interazioni di gioco client-side |
| `brick/math` | **Consigliato**: confronto risposte numeriche aperte con tolleranza |

### 3.6 Drive

| Dipendenza | Note |
|---|---|
| `league/flysystem-aws-s3-v3` (core) | File su MinIO, download con URL firmati temporanei |
| `setasign/fpdi` + `tecnickcom/tcpdf` | Validazione PDF e filigrana in post-upload (condiviso col modulo Filigrana) |
| Postgres full-text (`tsvector`) | Barra di ricerca — parti da qui; `laravel/scout` + Meilisearch solo se in futuro non basta |
| — | Versionamento "stile git": tabella `versioni`, nessun pacchetto |

### 3.7 Eventi

| Dipendenza | Note |
|---|---|
| `spatie/icalendar-generator` | Inviti .ics compatibili Google/Outlook |
| `spatie/laravel-medialibrary` (core) | Allegati evento |
| `maatwebsite/excel` (core) | Export risposte sondaggi |

### 3.8 Orientamento

Nessuna dipendenza extra: CRUD scuole/responsabili + Notifications + generazione mail (Blade). Integrazione Calendario via `spatie/icalendar-generator`.

### 3.9 Orari

| Dipendenza | Note |
|---|---|
| `symfony/dom-crawler` (core) | Scraping orari dal sito UNIPA |
| `spatie/browsershot` + Chromium headless | Rendering layout+palette → immagine PNG dell'orario (layout come template Blade/HTML) |
| `spatie/icalendar-generator` | Export orario verso calendario personale |

*Nota: Browsershot richiede Node + Puppeteer/Chromium nel container `app`.*

### 3.10 Aule Libere

| Dipendenza | Note |
|---|---|
| `leaflet` (npm) | Mappa posizione aula |
| `symfony/dom-crawler` (core) | Import iniziale aule + wrapper del portale UNIPA |

### 3.11 QR

| Dipendenza | Note |
|---|---|
| `qr-code-styling` (npm) | Generazione client-side con colore, forma dei punti, logo interno, export SVG/PNG/JPEG — copre tutti i requisiti |
| `endroid/qr-code` | Alternativa server-side se preferisci generare in PHP |

### 3.12 Calendario

| Dipendenza | Note |
|---|---|
| `spatie/icalendar-generator` | Inviti .ics via mail |
| Reverb/Echo (core) | Sync in tempo reale della dashboard embedded |

**Embedded (Raspberry Pi):** nessun firmware — è un browser in modalità kiosk (Chromium `--kiosk`) che punta a una pagina Livewire aggiornata via Echo.

### 3.13 Oggetti Smarriti

| Dipendenza | Note |
|---|---|
| `spatie/laravel-medialibrary` (core) | Foto oggetto |
| — | Integrazione Orari, copy annunci, mail mirate per corso: logica applicativa |
| `leaflet` (npm, extra) | Mappa università se si implementa l'extra |

### 3.14 Elezioni

| Dipendenza | Note |
|---|---|
| `maatwebsite/excel` (core) | Import liste persone da verificare, export risultati |
| — | Metodi di calcolo (D'Hondt, proporzionale...): implementali come classi PHP pure, ben testate con Pest — è il cuore del modulo, meglio non dipendere da librerie |

*Attenzione GDPR (dal preambolo): non memorizzare opinioni politiche — solo conteggi aggregati, mai il voto associato alla persona.*

### 3.15 Filigrana

| Dipendenza | Note |
|---|---|
| LibreOffice headless (`soffice --headless --convert-to pdf`) | Conversione .docx → .pdf, invocata via `Process` — nel container `app` |
| `setasign/fpdi` + `tecnickcom/tcpdf` | Applicazione filigrana (logo o testo, opacità ≥ 5%) |
| `ZipArchive` (nativo PHP) | Zip in caso di file multipli |
| Alpine.js (core) | Drag and drop upload |

### 3.16 Segnalazioni

| Dipendenza | Note |
|---|---|
| `spatie/laravel-medialibrary` (core) | Foto/video della problematica |
| `spatie/laravel-activitylog` (core) | Changelog append-only per segnalazione |
| — | Rilevamento duplicati: full-text Postgres su titolo/descrizione; mail precompilate: `mailto:` generato o Blade |

---

## 4. Riepilogo installazione

```bash
# Core
composer require nwidart/laravel-modules livewire/livewire filament/filament \
  laravel/fortify laravel/sanctum laravel/reverb laravel/horizon \
  spatie/laravel-permission spatie/laravel-medialibrary spatie/laravel-activitylog \
  laravel-notification-channels/webpush league/flysystem-aws-s3-v3 \
  maatwebsite/excel symfony/dom-crawler symfony/css-selector predis/predis \
  spatie/icalendar-generator setasign/fpdi tecnickcom/tcpdf \
  picqer/php-barcode-generator brick/money spatie/browsershot

composer require --dev pestphp/pest laravel/pint larastan/larastan laravel/telescope

npm install tailwindcss alpinejs laravel-echo pusher-js apexcharts leaflet qr-code-styling
```

## 5. Principi da mantenere

1. **Un solo `User`, schema `core`**: ogni modulo referenzia `core.users` con FK reali.
2. **Logica di dominio in Actions/Services**: controller web (Livewire) e controller API (embedded) chiamano lo stesso codice.
3. **Soldi sempre in centesimi interi** (`brick/money`), mai float.
4. **Niente cancellazioni fisiche** dove i documenti chiedono storicità (prodotti Kaffettino, oggetti smarriti, ruoli istituzionali, versioni Drive): flag `delisted`/`cestinato` + timestamp.
5. **Audit append-only**: activitylog su DB + canale Monolog dedicato su file per i requisiti "in OS", accesso solo super admin.
6. **Ogni scraping è un job isolato e schedulato**: se fallisce, non blocca nulla e notifica gli admin.