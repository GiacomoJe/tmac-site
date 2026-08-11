<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RepresentativeResource\Pages;
use App\Models\Representative;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RepresentativeResource extends Resource
{
    protected static ?string $model = Representative::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Comercial';

    protected static ?string $modelLabel = 'Representante';

    protected static ?string $pluralModelLabel = 'Representantes';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados')->schema([
                Forms\Components\TextInput::make('name')->label('Nome')->required()->maxLength(150),
                Forms\Components\TextInput::make('email')->label('E-mail')->email()->maxLength(150),
                Forms\Components\TextInput::make('phone')->label('Telefone')->maxLength(30),
                Forms\Components\TextInput::make('whatsapp')->label('WhatsApp (E.164)')->maxLength(30),
                Forms\Components\FileUpload::make('photo_path')->label('Foto')->image()->directory('representatives'),
                Forms\Components\Textarea::make('bio')->label('Bio')->rows(3),
                Forms\Components\Toggle::make('is_active')->label('Ativo')->default(true),
            ])->columns(2),

            Forms\Components\Section::make('Estados atendidos')->schema([
                Forms\Components\Select::make('states')->label('Estados (UF)')
                    ->relationship('states', 'name')
                    ->multiple()->searchable()->preload(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('photo_path')->label('Foto')->circular(),
                Tables\Columns\TextColumn::make('name')->label('Nome')->searchable(),
                Tables\Columns\TextColumn::make('whatsapp')->label('WhatsApp'),
                Tables\Columns\TextColumn::make('states.uf')->label('UFs')->badge(),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('is_active')->label('Ativo')])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRepresentatives::route('/'),
            'create' => Pages\CreateRepresentative::route('/create'),
            'edit' => Pages\EditRepresentative::route('/{record}/edit'),
        ];
    }
}
