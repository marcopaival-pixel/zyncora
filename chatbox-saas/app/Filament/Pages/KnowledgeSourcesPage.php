<?php

namespace App\Filament\Pages;

use App\Models\KnowledgeSource;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class KnowledgeSourcesPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?string $navigationGroup = 'Automação';

    protected static ?string $title = 'Fontes de Conhecimento';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.knowledge-sources-page';

    public $sources = [];

    public function mount()
    {
        $companyId = session('impersonated_company_id') ?? auth()->user()?->company_id;
        if ($companyId) {
            $this->sources = KnowledgeSource::where('company_id', $companyId)->get()->keyBy('source_type')->toArray();
        }
    }

    public function toggleSource($type)
    {
        $companyId = session('impersonated_company_id') ?? auth()->user()?->company_id;

        if (! $companyId) {
            Notification::make()
                ->title('Ação Inválida')
                ->body('Nenhuma organização identificada. Só é possível gerir fontes de conhecimento no contexto de um cliente (Empresa).')
                ->danger()
                ->send();

            return;
        }

        $source = KnowledgeSource::firstOrCreate(
            ['company_id' => $companyId, 'source_type' => $type],
            ['is_active' => false]
        );

        $source->is_active = ! $source->is_active;
        $source->save();

        $this->sources[$type] = $source->toArray();
    }
}
