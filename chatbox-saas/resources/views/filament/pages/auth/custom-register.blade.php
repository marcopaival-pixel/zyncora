<x-filament-panels::page.simple>
    <div class="fixed left-4 top-4 md:left-8 md:top-8 z-50">
        <a href="/" class="group inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/80 dark:bg-gray-800/80 backdrop-blur-md border border-gray-200 dark:border-gray-700 text-sm font-bold text-gray-600 dark:text-gray-300 transition-all hover:bg-white dark:hover:bg-gray-800 hover:text-primary-600 dark:hover:text-primary-400 shadow-sm hover:shadow-md">
            <x-heroicon-m-arrow-left class="w-4 h-4 transition-transform group-hover:-translate-x-1" />
            Voltar para o site
        </a>
    </div>

    <x-slot name="heading">
        {{ $this->getHeading() }}
    </x-slot>

    <x-slot name="subheading">
        {{ $this->getSubheading() }}

        {{ $this->loginAction }}
    </x-slot>

    <x-filament-panels::form wire:submit="register">
        {{ $this->form }}

        <x-filament-panels::form.actions
            :actions="$this->getCachedFormActions()"
            :full-width="$this->hasFullWidthFormActions()"
        />
    </x-filament-panels::form>
</x-filament-panels::page.simple>
