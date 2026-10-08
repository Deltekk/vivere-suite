{{-- Popup delle novità: App\Livewire\ChangelogModal --}}
<div>
    @if ($entries !== [])
        <x-filament::modal id="vivere-changelog" width="2xl" icon="heroicon-o-sparkles" :close-by-clicking-away="false" :close-button="false">
            <x-slot name="heading">Novità</x-slot>

            <div class="space-y-6">
                @foreach ($entries as $entry)
                    <section>
                        <h3 class="text-base font-semibold">{{ $entry['title'] }}</h3>
                        <p class="text-xs text-gray-500">{{ $entry['date'] }}</p>
                        <div class="fi-prose mt-2 text-sm">{!! $entry['html'] !!}</div>
                    </section>
                @endforeach
            </div>

            <x-slot name="footerActions">
                <x-filament::button wire:click="markAsSeen">Ho capito</x-filament::button>
            </x-slot>
        </x-filament::modal>

        {{-- Apre il popup appena la pagina è pronta --}}
        <div x-data x-init="$nextTick(() => $dispatch('open-modal', { id: 'vivere-changelog' }))"></div>
    @endif
</div>
