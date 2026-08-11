<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteRequestResource\Pages;
use App\Mail\QuoteRequestForwarded;
use App\Models\QuoteRequest;
use App\Models\Representative;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class QuoteRequestResource extends Resource
{
    protected static ?string $model = QuoteRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Comercial';

    protected static ?string $modelLabel = 'Solicitação de cotação';

    protected static ?string $pluralModelLabel = 'Solicitações de cotação';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'code';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'pending')->count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificação')->schema([
                Forms\Components\TextInput::make('code')->label('Código')->disabled(),
                Forms\Components\Select::make('status')->label('Status')->options([
                    'pending' => 'Pendente',
                    'in_progress' => 'Em atendimento',
                    'won' => 'Ganha',
                    'lost' => 'Perdida',
                ])->required(),
                Forms\Components\Select::make('assigned_representative_id')->label('Representante')
                    ->relationship('representative', 'name')->searchable(),
            ])->columns(3),

            Forms\Components\Section::make('Cliente')->schema([
                Forms\Components\TextInput::make('customer_name')->label('Comprador'),
                Forms\Components\TextInput::make('company')->label('Razão social'),
                Forms\Components\TextInput::make('cnpj')->label('CNPJ'),
                Forms\Components\Select::make('segment')->label('Segmento')->options([
                    'loja' => 'Loja de peças',
                    'oficina' => 'Oficina mecânica',
                    'concessionaria' => 'Concessionária',
                    'frota' => 'Frota / locadora',
                    'outro' => 'Outro',
                ]),
                Forms\Components\TextInput::make('email')->label('E-mail'),
                Forms\Components\TextInput::make('phone')->label('Telefone'),
                Forms\Components\Select::make('state_id')->label('UF')->relationship('state', 'uf'),
                Forms\Components\TextInput::make('city')->label('Cidade'),
                Forms\Components\Textarea::make('message')->label('Mensagem')->rows(3)->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Itens')->schema([
                Forms\Components\Repeater::make('items')->relationship('items')->label('Itens da cotação')
                    ->schema([
                        Forms\Components\TextInput::make('product_name_snapshot')->label('Produto')->disabled(),
                        Forms\Components\TextInput::make('product_sku_snapshot')->label('SKU')->disabled(),
                        Forms\Components\TextInput::make('quantity')->label('Qtd')->numeric(),
                        Forms\Components\Textarea::make('notes')->label('Obs')->rows(2)->columnSpanFull(),
                    ])->columns(3)->disabled()->collapsed(),
            ]),

            Forms\Components\Section::make('Origem')->schema([
                Forms\Components\KeyValue::make('utm_payload')->label('UTM')->disabled(),
                Forms\Components\TextInput::make('source')->disabled(),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Código')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('customer_name')->label('Cliente')->searchable(),
                Tables\Columns\TextColumn::make('company')->label('Empresa')->toggleable(),
                Tables\Columns\TextColumn::make('state.uf')->label('UF')->badge(),
                Tables\Columns\TextColumn::make('items_count')->counts('items')->label('Itens'),
                Tables\Columns\TextColumn::make('representative.name')->label('Vendedor')->toggleable()
                    ->placeholder('—')
                    ->description(fn ($record) => $record->forwarded_to_representative_at
                        ? 'Enviado em ' . $record->forwarded_to_representative_at->format('d/m H:i')
                        : null),
                Tables\Columns\TextColumn::make('forwarded_count')->label('Envios')->toggleable(isToggledHiddenByDefault: true)->badge(),
                Tables\Columns\BadgeColumn::make('status')->label('Status')->colors([
                    'warning' => 'pending',
                    'info' => 'in_progress',
                    'success' => 'won',
                    'danger' => 'lost',
                ]),
                Tables\Columns\TextColumn::make('created_at')->label('Criado em')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options([
                    'pending' => 'Pendente',
                    'in_progress' => 'Em atendimento',
                    'won' => 'Ganha',
                    'lost' => 'Perdida',
                ]),
                Tables\Filters\SelectFilter::make('state_id')->relationship('state', 'uf')->label('UF'),
            ])
            ->actions([
                Tables\Actions\Action::make('forward')
                    ->label('Enviar p/ vendedor')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->modalHeading(fn ($record) => "Encaminhar cotação #{$record->code}")
                    ->modalSubmitActionLabel('Enviar e-mail agora')
                    ->modalWidth('lg')
                    ->form([
                        Forms\Components\Select::make('representative_id')
                            ->label('Vendedor / Representante')
                            ->options(fn () => Representative::where('is_active', true)
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn ($r) => [
                                    $r->id => $r->name . ($r->email ? " · {$r->email}" : ' · (sem e-mail)'),
                                ]))
                            ->default(fn ($record) => $record->assigned_representative_id)
                            ->searchable()
                            ->required()
                            ->helperText('Lista todos os representantes ativos. O e-mail será enviado para o endereço cadastrado.'),

                        Forms\Components\Textarea::make('admin_message')
                            ->label('Observação para o vendedor (opcional)')
                            ->rows(3)
                            ->placeholder('Ex: Cliente já é da casa, prioridade alta. / Verificar disponibilidade do SKU XYZ antes de retornar.')
                            ->maxLength(1000),
                    ])
                    ->action(function ($record, array $data) {
                        $rep = Representative::find($data['representative_id']);
                        if (! $rep) {
                            Notification::make()->title('Vendedor não encontrado')->danger()->send();
                            return;
                        }
                        if (! $rep->email) {
                            Notification::make()
                                ->title('Sem e-mail cadastrado')
                                ->body("O vendedor {$rep->name} não tem e-mail. Cadastre primeiro em Representantes.")
                                ->danger()
                                ->send();
                            return;
                        }

                        try {
                            Mail::to($rep->email)->send(new QuoteRequestForwarded(
                                $record->fresh(['items', 'state']),
                                $rep,
                                $data['admin_message'] ?: null,
                            ));

                            $record->update([
                                'assigned_representative_id'     => $rep->id,
                                'forwarded_to_representative_at' => now(),
                                'forwarded_count'                => $record->forwarded_count + 1,
                                'status' => $record->status === 'pending' ? 'in_progress' : $record->status,
                            ]);

                            Notification::make()
                                ->title('Cotação encaminhada!')
                                ->body("E-mail enviado para {$rep->name} ({$rep->email}).")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            logger()->error('Quote forward failed: '.$e->getMessage());
                            Notification::make()
                                ->title('Falha ao enviar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuoteRequests::route('/'),
            'view' => Pages\ViewQuoteRequest::route('/{record}'),
            'edit' => Pages\EditQuoteRequest::route('/{record}/edit'),
        ];
    }
}
