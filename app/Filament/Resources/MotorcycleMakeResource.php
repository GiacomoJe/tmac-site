<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MotorcycleMakeResource\Pages;
use App\Models\MotorcycleMake;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MotorcycleMakeResource extends Resource
{
    protected static ?string $model = MotorcycleMake::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    protected static ?string $navigationGroup = 'Compatibilidade';

    protected static ?string $modelLabel = 'Marca de moto';

    protected static ?string $pluralModelLabel = 'Marcas de moto';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Marca')->schema([
                Forms\Components\TextInput::make('name')->label('Nome')->required()->maxLength(120)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                Forms\Components\TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true)->maxLength(140),
                Forms\Components\TextInput::make('country')->label('País de origem')->maxLength(60),
                Forms\Components\FileUpload::make('logo_path')->label('Logo')->image()
                    ->directory('motorcycle-makes')->imageEditor(),
            ])->columns(2),

            Forms\Components\Section::make('Exibição')->schema([
                Forms\Components\Toggle::make('is_active')->label('Ativa')->default(true),
                Forms\Components\TextInput::make('sort_order')->label('Ordem')->numeric()->default(0),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')->label('Logo'),
                Tables\Columns\TextColumn::make('name')->label('Marca')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('country')->label('País'),
                Tables\Columns\TextColumn::make('models_count')->counts('models')->label('Modelos'),
                Tables\Columns\IconColumn::make('is_active')->label('Ativa')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([Tables\Filters\TernaryFilter::make('is_active')->label('Ativa')])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMotorcycleMakes::route('/'),
            'create' => Pages\CreateMotorcycleMake::route('/create'),
            'edit' => Pages\EditMotorcycleMake::route('/{record}/edit'),
        ];
    }
}
