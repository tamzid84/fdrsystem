<x-filament::page>

    <div class="grid grid-cols-4 gap-4 mb-6">
        <x-filament::card>
            <h2>Total FDR</h2>
            <p>{{ $this->getFdrStats()['total_fdr'] }}</p>
        </x-filament::card>

        <x-filament::card>
            <h2>Active FDR</h2>
            <p>{{ $this->getFdrStats()['active_fdr'] }}</p>
        </x-filament::card>

        <x-filament::card>
            <h2>Encashed</h2>
            <p>{{ $this->getFdrStats()['encashed'] }}</p>
        </x-filament::card>

        <x-filament::card>
            <h2>Total Investment</h2>
            <p>{{ $this->getFdrStats()['total_investment'] }}</p>
        </x-filament::card>
    </div>

    {{-- EXPORT BUTTONS --}}
    <div class="flex gap-3 mb-6">
        <x-filament::button wire:click="exportExcel" color="success">
            Export Excel
        </x-filament::button>

        <x-filament::button wire:click="exportPdf" color="danger">
            Export PDF
        </x-filament::button>

        <x-filament::button wire:click="exportWord" color="primary">
            Export Word
        </x-filament::button>
    </div>

    {{-- TABLE RENDERED AUTOMATICALLY --}}
    {{ $this->table }}

</x-filament::page>