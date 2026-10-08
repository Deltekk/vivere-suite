{{-- Schermata di attesa: Modules\HumanResources\Filament\Pages\AwaitingApproval --}}
<x-filament-panels::page>
    <x-filament::section icon="heroicon-o-clock" icon-color="warning">
        <x-slot name="heading">
            {{ $this->user()->inflect('Benvenuto', 'Benvenuta', 'Ti diamo il benvenuto') }}, {{ $this->user()->name }}!
        </x-slot>

        <div class="space-y-3 text-sm">
            <p>La tua registrazione è arrivata ed è <strong>in attesa</strong>: qualcuno dello staff deve verificarla prima che tu possa usare i servizi della Suite Vivere.</p>
            <p>Ti avviseremo per mail a <strong>{{ $this->user()->email }}</strong> appena l'account sarà attivo.</p>
            @unless ($this->user()->hasVerifiedEmail())
                <p>Nel frattempo controlla la posta: ti abbiamo mandato un link per verificare l'indirizzo email.</p>
            @endunless
        </div>
    </x-filament::section>
</x-filament-panels::page>
