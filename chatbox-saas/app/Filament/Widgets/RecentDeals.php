<?php

namespace App\Filament\Widgets;

use App\Filament\Widgets\Concerns\RequiresCompanyOrPlatformAdmin;
use App\Filament\Pages\CRMBoard;
use App\Filament\Resources\DealResource;
use App\Models\Deal;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentDeals extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    use RequiresCompanyOrPlatformAdmin;

    protected static bool $isLazy = true;

    protected static ?int $sort = 9;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $heading = 'Negócios recentes';

    public function table(Table $table): Table
    {
        return $table
            ->description('Oportunidades ordenadas pela última alteração.')
            ->emptyStateHeading('Nenhum negócio')
            ->emptyStateDescription('Crie um negócio no CRM ou pelo pipeline visual.')
            ->query(function (): Builder {
                $query = Deal::query()->with(['contact', 'stage.pipeline', 'company']);

                $user = auth()->user();
                if ($user && ! $user->isPlatformAdmin()) {
                    $query->where('company_id', $user->company_id);
                }

                return $query->latest('updated_at');
            })
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Negócio')
                    ->limit(40),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->visible(fn (): bool => (bool) auth()->user()?->isPlatformAdmin())
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('contact.name')
                    ->label('Contato')
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('stage.name')
                    ->label('Etapa')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('value')
                    ->label('Valor')
                    ->money('BRL', 0, 'pt_BR'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->since(),
            ])
            ->recordUrl(fn (Deal $record): string => DealResource::getUrl('edit', ['record' => $record]))
            ->defaultSort('updated_at', 'desc')
            ->paginated([5])
            ->defaultPaginationPageOption(5)
            ->headerActions([
                Action::make('crm')
                    ->label('Quadro de negócios')
                    ->url(DealResource::getUrl('index'))
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->color('gray')
                    ->size('sm'),
                Action::make('kanban')
                    ->label('Pipeline visual')
                    ->url(CRMBoard::getUrl())
                    ->icon('heroicon-m-view-columns')
                    ->color('gray')
                    ->size('sm'),
            ]);
    }
}

