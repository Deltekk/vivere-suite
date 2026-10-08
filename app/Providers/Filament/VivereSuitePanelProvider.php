<?php

namespace App\Providers\Filament;

use App\Http\Middleware\AuthenticateWithHumanResources;
use App\Http\Middleware\EnsureAccountIsReady;
use App\Support\VivereColors;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Base comune di TUTTI i panel Filament della suite: da qui passa la standardizzazione della UI.
 *
 * Ogni modulo con un'interfaccia crea il proprio PanelProvider estendendo questa classe e
 * implementa solo configureModule() (id, path, nome, componenti). Tutto il resto (tema,
 * colori, notifiche, middleware, link comuni) è definito qui una volta sola, così ogni
 * piattaforma ha lo stesso aspetto e gli stessi requisiti trasversali del preambolo.
 *
 * Esempio: Modules/Kaffettino/app/Providers/Filament/KaffettinoPanelProvider.php
 */
abstract class VivereSuitePanelProvider extends PanelProvider
{
    /**
     * Configurazione specifica del modulo: ->id(), ->path(), ->brandName() e i componenti.
     */
    abstract protected function configureModule(Panel $panel): Panel;

    /**
     * final: i moduli non devono poter saltare la configurazione comune.
     */
    final public function panel(Panel $panel): Panel
    {
        return $this->configureModule($this->configureSuiteDefaults($panel));
    }

    /**
     * Registra resource, pagine e widget che si trovano in Modules/<Modulo>/app/Filament.
     */
    protected function discoverModuleComponents(Panel $panel, string $module): Panel
    {
        $directory = module_path($module, 'app/Filament');
        $namespace = "Modules\\{$module}\\Filament";

        return $panel
            ->discoverResources(in: "{$directory}/Resources", for: "{$namespace}\\Resources")
            ->discoverPages(in: "{$directory}/Pages", for: "{$namespace}\\Pages")
            ->discoverWidgets(in: "{$directory}/Widgets", for: "{$namespace}\\Widgets");
    }

    private function configureSuiteDefaults(Panel $panel): Panel
    {
        return $panel
            // ----- Aspetto (uguale per tutte le piattaforme) -----
            // Palette ufficiale di Vivere (dettagli e scelte in App\Support\VivereColors).
            // "secondary" è un colore in più del brand: si usa con color="secondary" nei componenti.
            ->colors([
                'primary' => VivereColors::Primary,
                'secondary' => VivereColors::Secondary,
                'warning' => VivereColors::Warning,
                'gray' => VivereColors::Gray,
                // Colori di supporto, scelti per stare bene con il blu del brand
                'success' => Color::Emerald,
                'danger' => Color::Rose,
                'info' => Color::Sky,
            ])
            // Unico tema CSS (Tailwind 4) per tutta la suite: resources/css/filament/theme.css
            ->viteTheme('resources/css/filament/theme.css')
            ->unsavedChangesAlerts()

            // ----- Requisiti trasversali del preambolo -----
            // Notifiche in piattaforma (campanella), salvate in core.notifications
            ->databaseNotifications()
            // Link per passare da una piattaforma all'altra
            ->renderHook(PanelsRenderHook::USER_MENU_BEFORE, fn (): string => view('filament.platform-switcher')->render())
            // Link alla pagina dei suggerimenti, presente in ogni piattaforma
            ->userMenuItems([
                'suggestions' => Action::make('suggestions')
                    ->label('Suggerimenti')
                    ->icon(Heroicon::OutlinedLightBulb)
                    ->url(fn (): ?string => config('vivere.suggestions_url'), shouldOpenInNewTab: true)
                    ->visible(fn (): bool => filled(config('vivere.suggestions_url'))),
            ])
            // Popup con le novità della piattaforma non ancora viste (App\Livewire\ChangelogModal)
            ->renderHook(PanelsRenderHook::BODY_END, fn (): string => Filament::auth()->check()
                ? Blade::render('@livewire(\'changelog-modal\', [\'panelId\' => $panelId])', ['panelId' => Filament::getCurrentPanel()?->getId() ?? ''])
                : '')

            // ----- Middleware -----
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            // Chi non è loggato viene mandato al login di HR, da qualunque piattaforma arrivi;
            // poi l'account deve essere "pronto" (accettato, profilo staff, mail UNIPA, anno confermato)
            ->authMiddleware([
                AuthenticateWithHumanResources::class,
                EnsureAccountIsReady::class,
            ]);
    }
}
