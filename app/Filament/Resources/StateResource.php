<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StateResource\Pages;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StateResource extends Resource
{
    protected static ?string $model = State::class;

    protected static ?string $navigationIcon = 'heroicon-o-map';

    protected static ?string $navigationGroup = 'Cotação';

    protected static ?string $modelLabel = 'Estado (UF)';

    protected static ?string $pluralModelLabel = 'Estados / Mínimo de cotação';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificação')->schema([
                Forms\Components\TextInput::make('name')->label('Nome')->required()->maxLength(80),
                Forms\Components\TextInput::make('uf')->label('UF')->required()->length(2)->unique(ignoreRecord: true),
                Forms\Components\Select::make('region')->label('Região')
                    ->options([
                        'N' => 'Norte', 'NE' => 'Nordeste', 'CO' => 'Centro-Oeste',
                        'SE' => 'Sudeste', 'S' => 'Sul',
                    ])->required(),
            ])->columns(3),

            Forms\Components\Section::make('Mínimo de cotação')->schema([
                Forms\Components\TextInput::make('min_quote_value')
                    ->label('Valor mínimo (R$)')
                    ->helperText('Valor mínimo que o cliente desse estado precisa atingir no carrinho para conseguir solicitar uma cotação. Deixe em branco para usar o padrão global.')
                    ->numeric()
                    ->prefix('R$')
                    ->step('0.01')
                    ->minValue(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('uf')->label('UF')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label('Estado')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('region')->label('Região')->badge(),
                Tables\Columns\TextColumn::make('min_quote_value')
                    ->label('Mínimo de cotação')
                    ->money('BRL')
                    ->sortable()
                    ->placeholder('— (usa padrão)'),
                Tables\Columns\TextColumn::make('representatives_count')->counts('representatives')->label('Representantes'),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('region')->label('Região')
                    ->options(['N' => 'Norte', 'NE' => 'Nordeste', 'CO' => 'Centro-Oeste', 'SE' => 'Sudeste', 'S' => 'Sul']),
            ])
            ->actions([Tables\Actions\EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStates::route('/'),
            'edit' => Pages\EditState::route('/{record}/edit'),
        ];
    }
}
