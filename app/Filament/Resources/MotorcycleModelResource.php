<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MotorcycleModelResource\Pages;
use App\Models\MotorcycleModel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MotorcycleModelResource extends Resource
{
    protected static ?string $model = MotorcycleModel::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'Compatibilidade';

    protected static ?string $modelLabel = 'Modelo de moto';

    protected static ?string $pluralModelLabel = 'Modelos de moto';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Modelo')->schema([
                Forms\Components\Select::make('motorcycle_make_id')->label('Marca')
                    ->relationship('make', 'name')->searchable()->preload()->required(),
                Forms\Components\TextInput::make('name')->label('Nome')->required()->maxLength(140)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                Forms\Components\TextInput::make('slug')->label('Slug')->required()->maxLength(160),
                Forms\Components\Select::make('category')->label('Categoria')->options(MotorcycleModel::CATEGORIES),
                Forms\Components\TextInput::make('displacement')->label('Cilindrada (cc)')->numeric()
                    ->suffix('cc')->maxValue(3000),
                Forms\Components\FileUpload::make('image_path')->label('Imagem')->image()
                    ->directory('motorcycle-models')->imageEditor(),
            ])->columns(2),

            Forms\Components\Section::make('Anos de produção')->schema([
                Forms\Components\TextInput::make('year_start')->label('Ano de lançamento')->numeric()
                    ->minValue(1950)->maxValue((int) date('Y') + 1),
                Forms\Components\TextInput::make('year_end')->label('Último ano produzido')->numeric()
                    ->minValue(1950)->maxValue((int) date('Y') + 1)
                    ->helperText('Deixe em branco se ainda em linha'),
            ])->columns(2),

            Forms\Components\Section::make('Exibição')->schema([
                Forms\Components\Toggle::make('is_active')->label('Ativo')->default(true),
                Forms\Components\TextInput::make('sort_order')->label('Ordem')->numeric()->default(0),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')->label('Img'),
                Tables\Columns\TextColumn::make('make.name')->label('Marca')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('name')->label('Modelo')->searchable(),
                Tables\Columns\TextColumn::make('displacement')->label('cc')->suffix(' cc'),
                Tables\Columns\TextColumn::make('category')->label('Categoria')
                    ->formatStateUsing(fn ($state) => MotorcycleModel::CATEGORIES[$state] ?? $state),
                Tables\Columns\TextColumn::make('year_range')->label('Anos'),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
            ])
            ->defaultSort('motorcycle_make_id')
            ->filters([
                Tables\Filters\SelectFilter::make('motorcycle_make_id')->relationship('make', 'name')->label('Marca'),
                Tables\Filters\SelectFilter::make('category')->options(MotorcycleModel::CATEGORIES),
                Tables\Filters\TernaryFilter::make('is_active')->label('Ativo'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMotorcycleModels::route('/'),
            'create' => Pages\CreateMotorcycleModel::route('/create'),
            'edit' => Pages\EditMotorcycleModel::route('/{record}/edit'),
        ];
    }
}
