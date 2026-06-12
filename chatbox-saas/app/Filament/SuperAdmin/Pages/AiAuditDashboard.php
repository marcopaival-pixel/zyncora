<?php

namespace App\Filament\SuperAdmin\Pages;

use Filament\Pages\Page;
use App\Models\AiAnswerAuditLog;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

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
            \App\Filament\Widgets\SuperAdminAiConsumptionWidget::class,
            \App\Filament\Widgets\AiChatbotRankingWidget::class,
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
