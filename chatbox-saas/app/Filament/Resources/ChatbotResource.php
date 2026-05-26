<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChatbotResource\Pages;
use App\Filament\Resources\ChatbotResource\RelationManagers;
use App\Models\Chatbot;
use App\Services\PlanService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChatbotResource extends Resource
{
    protected static ?string $model = Chatbot::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Atendimento';

    protected static ?string $navigationLabel = 'Chatbots';

    protected static ?string $modelLabel = 'Chatbot';

    protected static ?string $pluralModelLabel = 'Chatbots';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    /** Linha secundária na lista (canal + origem lógica). */
    public static function formatChatbotListContext(Chatbot $record): ?string
    {
        $parts = [];
        if ($record->channel?->type) {
            $parts[] = 'Canal: '.$record->channel->type;
        }
        if (filled($record->default_channel)) {
            $parts[] = match ($record->default_channel) {
                'whatsapp' => 'Origem: WhatsApp',
                'site' => 'Origem: Site',
                'internal' => 'Origem: Sistema',
                default => 'Origem: '.$record->default_channel,
            };
        }

        return $parts !== [] ? implode(' · ', $parts) : null;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->hasPermission('view_chatbot') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermission('view_chatbot') ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['company', 'channel']);
        $user = auth()->user();
        if ($user && ! $user->isPlatformAdmin()) {
            $query->where('company_id', $user->company_id);
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identidade e ligação')
                    ->description('Nome do bot, empresa e canal padrão de entrada.')
                    ->icon('heroicon-o-sparkles')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->label('Empresa')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->visible(fn () => auth()->user()?->isPlatformAdmin() ?? false),
                        Forms\Components\TextInput::make('name')
                            ->label('Nome do chatbot')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Ex.: Atendimento comercial'),
                        Forms\Components\Select::make('channel_id')
                            ->relationship('channel', 'type')
                            ->label('Canal preferencial')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Opcional: associe a um canal já criado em Integrações.'),
                        Forms\Components\TextInput::make('whatsapp_phone')
                            ->label('Telefone WhatsApp (E.164)')
                            ->maxLength(64)
                            ->placeholder('+351...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Primeira impressão e horário')
                    ->description('Mensagem inicial e janela de atendimento.')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->schema([
                        Forms\Components\Textarea::make('initial_message')
                            ->label('Mensagem inicial')
                            ->rows(4)
                            ->columnSpanFull(),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TimePicker::make('hours_start')
                                    ->label('Início')
                                    ->seconds(false),
                                Forms\Components\TimePicker::make('hours_end')
                                    ->label('Fim')
                                    ->seconds(false),
                            ]),
                        Forms\Components\Select::make('default_channel')
                            ->label('Canal lógico padrão')
                            ->options([
                                'whatsapp' => 'WhatsApp',
                                'site' => 'Site',
                                'internal' => 'Sistema',
                            ])
                            ->default('site')
                            ->required()
                            ->native(false),
                    ]),

                Forms\Components\Section::make('Estado')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'active' => 'Ativo',
                                'inactive' => 'Inativo',
                            ])
                            ->default('inactive')
                            ->required()
                            ->native(false),
                    ]),

                Forms\Components\Section::make('Inteligência Artificial')
                    ->description('Configurações para respostas automáticas baseadas em IA.')
                    ->icon('heroicon-o-cpu-chip')
                    ->collapsible()
                    ->schema([
                        Forms\Components\Toggle::make('use_ai')
                            ->label('Ativar Cérebro de IA')
                            ->helperText(function (?Chatbot $record = null) {
                                if ($record && $record->company && ! app(PlanService::class)->canUseAi($record->company)) {
                                    return '⚠️ Esta funcionalidade não está disponível no plano '.strtoupper($record->company->plan).'. Faça o upgrade.';
                                }

                                return 'Se ativado, o bot tentará responder usando a base de conhecimento.';
                            })
                            ->disabled(function (?Chatbot $record = null) {
                                return $record && $record->company && ! app(PlanService::class)->canUseAi($record->company);
                            })
                            ->default(false),

                        Forms\Components\Textarea::make('ai_instruction')
                            ->label('Instrução Principal da IA (System Prompt)')
                            ->placeholder('Ex: Você é o atendente virtual da PetShop Amigo Fiel. Seja brincalhão e prestativo.')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Empresa')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->toggleable()
                    ->visible(fn (): bool => auth()->user()?->isPlatformAdmin() ?? false),

                Stack::make([
                    Tables\Columns\TextColumn::make('name')
                        ->label('Chatbot')
                        ->searchable()
                        ->sortable()
                        ->weight('semibold')
                        ->icon('heroicon-o-sparkles')
                        ->iconColor('primary')
                        ->description(fn (Chatbot $record): ?string => static::formatChatbotListContext($record)),

                    Tables\Columns\TextColumn::make('whatsapp_phone')
                        ->label('WhatsApp (E.164)')
                        ->searchable()
                        ->placeholder('—')
                        ->copyable()
                        ->copyMessage('Número copiado')
                        ->icon('heroicon-o-phone')
                        ->color('gray')
                        ->size('sm'),
                ])
                    ->space(2),

                Tables\Columns\TextColumn::make('channel.type')
                    ->label('Integração')
                    ->badge()
                    ->placeholder('—')
                    ->color('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('default_channel')
                    ->label('Origem')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'whatsapp' => 'WhatsApp',
                        'site' => 'Site / widget',
                        'internal' => 'Interno',
                        default => $state ?? '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'whatsapp' => 'success',
                        'site' => 'info',
                        'internal' => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('use_ai')
                    ->label('IA')
                    ->boolean()
                    ->trueIcon('heroicon-o-cpu-chip')
                    ->falseIcon('heroicon-o-minus-small')
                    ->trueColor('success')
                    ->alignCenter()
                    ->tooltip(fn (Chatbot $record): string => $record->use_ai ? 'Respostas com base de conhecimento' : 'Sem IA')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Atualizado')
                    ->since()
                    ->sortable()
                    ->tooltip(fn (Chatbot $record): ?string => $record->updated_at?->format('d/m/Y H:i')),
            ])
            ->recordUrl(fn (Chatbot $record): string => static::getUrl('edit', ['record' => $record]))
            ->defaultSort('updated_at', 'desc')
            ->searchPlaceholder('Pesquisar por nome, WhatsApp ou empresa…')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                    ]),
                Tables\Filters\TernaryFilter::make('use_ai')
                    ->label('Base de conhecimento / IA')
                    ->placeholder('Todos')
                    ->trueLabel('Com IA')
                    ->falseLabel('Sem IA'),
            ])
            ->actions([
                Tables\Actions\Action::make('test')
                    ->label('Testar')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->iconButton()
                    ->tooltip('Abrir consola de teste')
                    ->url(fn (Chatbot $record): string => static::getUrl('test', ['record' => $record])),
                Tables\Actions\Action::make('builder')
                    ->label('Fluxo')
                    ->icon('heroicon-o-rectangle-group')
                    ->color('warning')
                    ->iconButton()
                    ->tooltip('Editor de fluxo visual')
                    ->url(fn (Chatbot $record): string => static::getUrl('builder', ['record' => $record])),
                Tables\Actions\EditAction::make()
                    ->label('Editar'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Nenhum chatbot configurado')
            ->emptyStateDescription('Crie o primeiro assistente para ligar ao widget, WhatsApp ou fluxos automáticos. Use «Testar» e «Fluxo» assim que existir um registo.')
            ->emptyStateIcon('heroicon-o-sparkles')
            ->emptyStateActions([
                Tables\Actions\Action::make('createFromEmpty')
                    ->label('Criar chatbot')
                    ->icon('heroicon-o-plus')
                    ->url(static::getUrl('create'))
                    ->button(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ScriptStepsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChatbots::route('/'),
            'create' => Pages\CreateChatbot::route('/create'),
            'edit' => Pages\EditChatbot::route('/{record}/edit'),
            'builder' => Pages\FlowBuilder::route('/{record}/builder'),
            'test' => Pages\TestChat::route('/{record}/test'),
        ];
    }
}
