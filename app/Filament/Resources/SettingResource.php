<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SettingResource\Pages;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Sistema';

    protected static ?string $modelLabel = 'Configuração';

    protected static ?string $pluralModelLabel = 'Configurações';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('key')->label('Chave')->required()->unique(ignoreRecord: true),
            Forms\Components\TextInput::make('label')->label('Rótulo'),
            Forms\Components\Select::make('group')->label('Grupo')->options([
                'general' => 'Geral',
                'contact' => 'Contato',
                'social' => 'Redes Sociais',
                'marketing' => 'Marketing',
                'content' => 'Conteúdo',
            ])->default('general'),
            Forms\Components\Textarea::make('value')->label('Valor')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group')->label('Grupo')->badge()->sortable(),
                Tables\Columns\TextColumn::make('label')->label('Rótulo')->searchable(),
                Tables\Columns\TextColumn::make('key')->label('Chave')->fontFamily('mono')->copyable(),
                Tables\Columns\TextColumn::make('value')->label('Valor')->limit(40),
            ])
            ->defaultGroup('group')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
