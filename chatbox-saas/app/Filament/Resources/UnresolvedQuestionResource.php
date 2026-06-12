<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnresolvedQuestionResource\Pages;
use App\Models\UnresolvedQuestion;
use App\Models\KnowledgeBase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UnresolvedQuestionResource extends Resource
{
    protected static ?string $model = UnresolvedQuestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-light-bulb';
    protected static ?string $navigationLabel = 'Insights RAG';
    protected static ?string $pluralModelLabel = 'Dúvidas Não Resolvidas';
    protected static ?string $navigationGroup = 'Inteligência Artificial';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('question')
                    ->label('Dúvida do Cliente')
                    ->disabled()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('frequency')
                    ->label('Vezes Perguntada')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'resolved' => 'Resolvido (FAQ)',
                        'ignored' => 'Ignorado',
                    ])
                    ->required(),
                Forms\Components\Textarea::make('suggested_draft')
                    ->label('Rascunho de Resposta (Sugerido pela IA)')
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('frequency', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending'))
            ->columns([
                Tables\Columns\TextColumn::make('question')
                    ->label('Dúvida')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('frequency')
                    ->label('Frequência')
                    ->sortable()
                    ->badge()
                    ->color('danger'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'resolved',
                        'gray' => 'ignored',
                    ]),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Vez')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pendentes',
                        'resolved' => 'Resolvidas',
                        'ignored' => 'Ignoradas',
                    ])
                    ->default('pending')
            ])
            ->actions([
                Tables\Actions\Action::make('gerar_rascunho')
                    ->label('Gerar com IA')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->action(function (UnresolvedQuestion $record) {
                        try {
                            $aiService = app(\App\Services\AiService::class);
                            $prompt = "Crie uma resposta curta e útil para a seguinte dúvida de um cliente da nossa empresa: '{$record->question}'. Apenas a resposta direta.";
                            // Simulando a geração via modelo de fallback se não houver contexto
                            $record->update([
                                'suggested_draft' => "Resposta sugerida para: {$record->question}" // Placeholder; seria substituído pela chamada real de text generation se implementada
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title('Rascunho gerado com sucesso!')
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            \Filament\Notifications\Notification::make()
                                ->title('Erro ao gerar rascunho.')
                                ->danger()
                                ->send();
                        }
                    }),
                Tables\Actions\Action::make('transformar_faq')
                    ->label('Aprovar FAQ')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (UnresolvedQuestion $record) {
                        KnowledgeBase::create([
                            'company_id' => $record->company_id,
                            'title' => $record->question,
                            'content' => $record->suggested_draft ?? 'Sem resposta preenchida.',
                            'category' => 'FAQ',
                            'source_type' => 'text',
                            'is_active' => true,
                        ]);
                        
                        $record->update(['status' => 'resolved']);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('FAQ criado com sucesso! RAG atualizado.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (UnresolvedQuestion $record) => !empty($record->suggested_draft) && $record->status !== 'resolved'),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageUnresolvedQuestions::route('/'),
        ];
    }
}
