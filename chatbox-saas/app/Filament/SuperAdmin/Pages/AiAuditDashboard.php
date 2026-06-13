<?php

namespace App\Filament\SuperAdmin\Pages;

use App\Filament\Widgets\AiChatbotRankingWidget;
use App\Filament\Widgets\SuperAdminAiConsumptionWidget;
use App\Models\AiAnswerAuditLog;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class AiAuditDashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Relatórios';

    protected static ?string $title = 'Auditoria do Orquestrador';

    protected static string $view = 'filament.pages.ai-audit-dashboard';

    protected function getHeaderWidgets(): array
    {
        return [
            SuperAdminAiConsumptionWidget::class,
            AiChatbotRankingWidget::class,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(AiAnswerAuditLog::query()->latest())
            ->columns([
                TextColumn::make('created_at')->label('Data')->dateTime('d/m/Y H:i'),
                TextColumn::make('user_message')->label('Pergunta')->limit(50),
                TextColumn::make('source_used')
                    ->label('Fonte Utilizada')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'llm_generative' => 'danger',
                        'faq' => 'success',
                        'company_data' => 'info',
                        'external_api' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('tokens_saved_estimated')->label('Tokens Economizados (Est.)'),
            ])
            ->defaultPaginationPageOption(10);
    }
}
