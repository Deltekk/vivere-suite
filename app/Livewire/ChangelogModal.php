<?php

namespace App\Livewire;

use App\Models\Changelog;
use App\Models\User;
use App\Support\Suite;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Popup con le novità della piattaforma non ancora viste (preambolo: "far visualizzare i
 * changelog al primo login utile in una qualsiasi piattaforma").
 *
 * È inserito in tutti i panel da VivereSuitePanelProvider. Quando l'utente chiude il popup
 * le voci vengono segnate come viste (core.changelog_user) e non compaiono più.
 */
class ChangelogModal extends Component
{
    /** Id del panel in cui è mostrato (hr, kaffettino, ...). */
    public string $panelId;

    /** @var list<array{id: string, title: string, html: string, date: string}> */
    public array $entries = [];

    public function mount(string $panelId): void
    {
        $this->panelId = $panelId;
        $user = Filament::auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $this->entries = array_values(Changelog::query()
            ->where('service', Suite::serviceForPanel($panelId))
            ->where('published_at', '<=', now())
            ->whereDoesntHave('viewers', fn (Builder $query) => $query->whereKey($user->getKey()))
            ->orderBy('published_at')
            ->limit(10)
            ->get()
            ->map(fn (Changelog $changelog): array => [
                'id' => $changelog->id,
                'title' => $changelog->title,
                // Markdown scritto dagli admin: l'HTML grezzo e i link pericolosi vengono eliminati
                'html' => (string) Str::markdown($changelog->body, ['html_input' => 'strip', 'allow_unsafe_links' => false]),
                'date' => $changelog->published_at->setTimezone(config('vivere.display_timezone'))->format('d/m/Y'),
            ])
            ->all());
    }

    public function markAsSeen(): void
    {
        $user = Filament::auth()->user();

        if ($user instanceof User && $this->entries !== []) {
            $user->seenChangelogs()->syncWithoutDetaching(
                collect($this->entries)->mapWithKeys(fn (array $entry): array => [$entry['id'] => ['seen_at' => now()]])->all(),
            );
        }

        $this->entries = [];
        $this->dispatch('close-modal', id: 'vivere-changelog');
    }

    public function render(): View
    {
        return view('livewire.changelog-modal');
    }
}
