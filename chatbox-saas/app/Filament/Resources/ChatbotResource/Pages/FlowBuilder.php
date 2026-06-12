<?php

namespace App\Filament\Resources\ChatbotResource\Pages;

use App\Filament\Resources\ChatbotResource;
use App\Models\ChatbotFlowTemplate;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\Attributes\Computed;

class FlowBuilder extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ChatbotResource::class;

    protected static string $view = 'filament.resources.chatbot-resource.pages.flow-builder';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getHeading(): string|Htmlable
    {
        return 'Construtor de fluxo';
    }

    public function getSubheading(): string|Htmlable|null
    {
        if ($this->record === null) {
            return null;
        }

        return $this->record->name.' — Arraste blocos para o canvas, ligue os nós e use as ações Salvar ou Publicar quando estiver pronto.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Salvar rascunho')
                ->color('primary')
                ->icon('heroicon-o-check-circle')
                ->action(fn () => $this->dispatch('save-flow')),
            Action::make('publish')
                ->label('Publicar fluxo')
                ->color('success')
                ->icon('heroicon-o-rocket-launch')
                ->requiresConfirmation()
                ->modalHeading('Publicar este fluxo?')
                ->modalDescription('O fluxo atualmente guardado passa a ser o que os visitantes seguem nas conversas reais (após publicação).')
                ->action(fn () => $this->publishFlow()),
            Action::make('test')
                ->label('Testar chat')
                ->color('info')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->url(fn () => $this->getResource()::getUrl('test', ['record' => $this->record])),
            Action::make('back')
                ->label('Editar chatbot')
                ->color('gray')
                ->icon('heroicon-o-arrow-left')
                ->url(fn () => $this->getResource()::getUrl('edit', ['record' => $this->record])),
        ];
    }

    public function saveFlow($data)
    {
        $this->record->update([
            'flow_data' => $data,
        ]);

        Notification::make()
            ->title('Rascunho guardado')
            ->body('O fluxo foi atualizado. Publique quando quiser aplicar em produção.')
            ->success()
            ->send();
    }

    public function getTemplatesProperty()
    {
        return ChatbotFlowTemplate::query()
            ->where(function ($query): void {
                $query->where('is_public', true);
                $companyId = auth()->user()?->company_id;
                if ($companyId !== null) {
                    $query->orWhere('company_id', $companyId);
                }
            })
            ->orderBy('name')
            ->get();
    }

    public function publishFlow()
    {
        $this->record->update([
            'published_flow_data' => $this->record->flow_data,
        ]);

        Notification::make()
            ->title('Fluxo publicado')
            ->body('As conversas passam a usar esta versão do fluxo.')
            ->success()
            ->send();
    }

    #[Computed]
    public function getNodeStatsProperty()
    {
        return \App\Models\ChatbotFlowNodeStat::query()
            ->where('chatbot_id', $this->record->id)
            ->selectRaw('node_id, sum(views) as total_views, sum(transfers) as total_transfers, sum(dropoffs) as total_dropoffs')
            ->groupBy('node_id')
            ->get()
            ->keyBy('node_id')
            ->toArray();
    }
}
