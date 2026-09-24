<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ResellerResource\Pages;
use App\Models\Reseller;
use App\Models\State;
use App\Models\TmacBrand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Http;

class ResellerResource extends Resource
{
    protected static ?string $model = Reseller::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'Comercial';

    protected static ?string $modelLabel = 'Revendedor';

    protected static ?string $pluralModelLabel = 'Revendedores';

    protected static ?int $navigationSort = 4;

    public static function getNavigationBadge(): ?string
    {
        $semGeo = static::getModel()::where('is_active', true)
            ->where(fn ($q) => $q->whereNull('latitude')->orWhereNull('longitude'))
            ->count();

        return $semGeo > 0 ? (string) $semGeo : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Revendedores sem coordenadas (não aparecem no mapa)';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificação')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome da loja')
                    ->required()
                    ->maxLength(160)
                    ->columnSpan(2),
                Forms\Components\TextInput::make('company_name')->label('Razão social')->maxLength(160),
                Forms\Components\TextInput::make('cnpj')->label('CNPJ')->maxLength(20),
            ])->columns(2),

            Forms\Components\Section::make('Endereço')
                ->description('Preencha o CEP e clique em "Buscar CEP" para completar automaticamente.')
                ->schema([
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\TextInput::make('zip')
                            ->label('CEP')
                            ->maxLength(12)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('buscarCep')
                                    ->label('Buscar CEP')
                                    ->icon('heroicon-m-magnifying-glass')
                                    ->action(function ($state, Forms\Set $set) {
                                        $cep = preg_replace('/\D/', '', (string) $state);
                                        if (strlen($cep) !== 8) {
                                            Notification::make()->title('CEP inválido')->warning()->send();
                                            return;
                                        }
                                        try {
                                            $via = Http::timeout(8)->get("https://viacep.com.br/ws/{$cep}/json/")->json();
                                            if (! empty($via['erro'])) {
                                                Notification::make()->title('CEP não encontrado')->warning()->send();
                                                return;
                                            }
                                            $set('address', $via['logradouro'] ?? null);
                                            $set('neighborhood', $via['bairro'] ?? null);
                                            $set('city', $via['localidade'] ?? null);

                                            if (! empty($via['uf'])) {
                                                $stateId = State::where('uf', $via['uf'])->value('id');
                                                if ($stateId) $set('state_id', $stateId);
                                            }
                                            Notification::make()->title('Endereço preenchido')->success()->send();
                                        } catch (\Throwable $e) {
                                            Notification::make()->title('Falha ao consultar CEP')->danger()->send();
                                        }
                                    })
                            ),
                        Forms\Components\TextInput::make('address')->label('Rua / Avenida')->maxLength(200)->columnSpan(2),
                        Forms\Components\TextInput::make('number')->label('Número')->maxLength(20),
                    ]),

                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\TextInput::make('complement')->label('Complemento')->maxLength(100),
                        Forms\Components\TextInput::make('neighborhood')->label('Bairro')->maxLength(120),
                        Forms\Components\TextInput::make('city')->label('Cidade')->required()->maxLength(140),
                        Forms\Components\Select::make('state_id')
                            ->label('Estado')
                            ->options(State::orderBy('name')->pluck('name', 'id'))
                            ->searchable(),
                    ]),
                ]),

            Forms\Components\Section::make('Localização no mapa')
                ->description('Sem coordenadas o revendedor NÃO aparece no mapa. Use o botão para buscar automaticamente.')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric()
                            ->step('0.0000001'),
                        Forms\Components\TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric()
                            ->step('0.0000001'),
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('geocodificar')
                                ->label('Localizar no mapa')
                                ->icon('heroicon-m-map-pin')
                                ->color('primary')
                                ->action(function (Forms\Get $get, Forms\Set $set) {
                                    $uf = $get('state_id') ? State::find($get('state_id'))?->uf : null;

                                    $query = implode(', ', array_filter([
                                        trim(($get('address') ?? '').' '.($get('number') ?? '')),
                                        $get('neighborhood'),
                                        $get('city'),
                                        $uf,
                                        'Brasil',
                                    ]));

                                    if (mb_strlen($query) < 8) {
                                        Notification::make()->title('Preencha o endereço primeiro')->warning()->send();
                                        return;
                                    }

                                    try {
                                        $res = Http::timeout(12)
                                            ->withHeaders(['User-Agent' => 'TMAC-Import/1.0 (contato@tmacimport.com.br)'])
                                            ->get('https://nominatim.openstreetmap.org/search', [
                                                'q' => $query, 'format' => 'json', 'limit' => 1, 'countrycodes' => 'br',
                                            ])
                                            ->json();

                                        if (empty($res)) {
                                            Notification::make()
                                                ->title('Endereço não localizado')
                                                ->body('Tente sem o número, ou preencha lat/lng manualmente pelo Google Maps.')
                                                ->warning()->send();
                                            return;
                                        }

                                        $set('latitude', round((float) $res[0]['lat'], 7));
                                        $set('longitude', round((float) $res[0]['lon'], 7));

                                        Notification::make()->title('Coordenadas encontradas!')->success()->send();
                                    } catch (\Throwable $e) {
                                        Notification::make()->title('Falha na busca')->danger()->send();
                                    }
                                }),
                        ])->verticalAlignment('end'),
                    ]),

                    Forms\Components\Placeholder::make('dica_maps')
                        ->label('')
                        ->content(new \Illuminate\Support\HtmlString(
                            '<span class="text-sm text-gray-500">Alternativa manual: abra o Google Maps, clique com o botão direito no local exato e copie as coordenadas (ex: <code>-23.5505, -46.6333</code>).</span>'
                        )),
                ]),

            Forms\Components\Section::make('Contato')->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('phone')->label('Telefone')->maxLength(30),
                    Forms\Components\TextInput::make('whatsapp')->label('WhatsApp')->maxLength(30)
                        ->helperText('Com DDD. Ex: 11999998888'),
                    Forms\Components\TextInput::make('email')->label('E-mail')->email()->maxLength(160),
                    Forms\Components\TextInput::make('website')->label('Site')->url()->maxLength(255),
                    Forms\Components\TextInput::make('instagram')->label('Instagram')->maxLength(120)
                        ->prefix('@')->helperText('Sem o @'),
                    Forms\Components\TextInput::make('opening_hours')->label('Horário de funcionamento')
                        ->maxLength(255)->placeholder('Seg-Sex 8h-18h · Sáb 8h-12h'),
                ]),
            ])->collapsed(),

            Forms\Components\Section::make('Exibição')->schema([
                Forms\Components\Select::make('tmac_brands')
                    ->label('Linhas que revende')
                    ->multiple()
                    ->options(fn () => TmacBrand::active()->ordered()->pluck('name', 'slug'))
                    ->helperText('Opcional — para futuros filtros por linha.'),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('sort_order')->label('Ordem')->numeric()->default(0),
                    Forms\Components\Toggle::make('is_featured')->label('Parceiro destaque')->inline(false)
                        ->helperText('Marcador vermelho no mapa'),
                    Forms\Components\Toggle::make('is_active')->label('Ativo')->default(true)->inline(false),
                ]),
                Forms\Components\Textarea::make('notes')->label('Observações internas')->rows(2)
                    ->helperText('Não aparece no site.'),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Loja')
                    ->searchable()
                    ->sortable()
                    ->description(fn ($record) => $record->company_name),
                Tables\Columns\TextColumn::make('city')->label('Cidade')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('state.uf')->label('UF')->badge()->sortable(),
                Tables\Columns\TextColumn::make('phone')->label('Contato')
                    ->description(fn ($record) => $record->whatsapp)
                    ->toggleable(),
                Tables\Columns\IconColumn::make('has_geo')
                    ->label('Mapa')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->latitude !== null && $record->longitude !== null)
                    ->trueIcon('heroicon-o-map-pin')
                    ->falseIcon('heroicon-o-exclamation-triangle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn ($record) => $record->latitude ? 'Aparece no mapa' : 'SEM coordenadas — não aparece'),
                Tables\Columns\IconColumn::make('is_featured')->label('Destaque')->boolean()->toggleable(),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('state_id')
                    ->relationship('state', 'name')
                    ->label('Estado'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Ativo'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Destaque'),
                Tables\Filters\Filter::make('sem_coordenadas')
                    ->label('Sem coordenadas')
                    ->query(fn ($q) => $q->whereNull('latitude')->orWhereNull('longitude')),
            ])
            ->actions([
                Tables\Actions\Action::make('ver_mapa')
                    ->label('')
                    ->icon('heroicon-o-globe-alt')
                    ->color('gray')
                    ->url(fn ($record) => $record->maps_url)
                    ->openUrlInNewTab()
                    ->visible(fn ($record) => $record->latitude !== null),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListResellers::route('/'),
            'create' => Pages\CreateReseller::route('/create'),
            'edit'   => Pages\EditReseller::route('/{record}/edit'),
        ];
    }
}
