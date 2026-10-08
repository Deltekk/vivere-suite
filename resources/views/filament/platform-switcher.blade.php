{{--
    Menu "Piattaforme" in alto a destra di ogni panel (registrato in VivereSuitePanelProvider).
    Elenca gli altri panel della suite a cui l'utente può accedere (User::canAccessPanel).
--}}
@php
    $user = filament()->auth()->user();
    $currentPanelId = filament()->getCurrentPanel()?->getId();

    $panels = collect(filament()->getPanels())
        ->reject(fn (\Filament\Panel $panel): bool => $panel->getId() === $currentPanelId)
        ->filter(fn (\Filament\Panel $panel): bool => (bool) $user?->canAccessPanel($panel));
@endphp

@if ($panels->isNotEmpty())
    <x-filament::dropdown placement="bottom-end" teleport>
        <x-slot name="trigger">
            <x-filament::icon-button
                :icon="\Filament\Support\Icons\Heroicon::OutlinedSquares2x2"
                label="Piattaforme Vivere"
                color="gray"
            />
        </x-slot>

        <x-filament::dropdown.list>
            @foreach ($panels as $panel)
                <x-filament::dropdown.list.item tag="a" :href="$panel->getUrl()">
                    {{ $panel->getBrandName() }}
                </x-filament::dropdown.list.item>
            @endforeach
        </x-filament::dropdown.list>
    </x-filament::dropdown>
@endif
