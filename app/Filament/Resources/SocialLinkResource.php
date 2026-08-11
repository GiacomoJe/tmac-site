<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SocialLinkResource\Pages;
use App\Models\SocialLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SocialLinkResource extends Resource
{
    protected static ?string $model = SocialLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-share';

    protected static ?string $navigationGroup = 'Conteúdo';

    protected static ?string $modelLabel = 'Rede social';

    protected static ?string $pluralModelLabel = 'Redes sociais';

    protected static ?int $navigationSort = 9;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\Select::make('platform')
                    ->label('Plataforma')
                    ->options(SocialLink::PLATFORMS)
                    ->required()
                    ->searchable()
                    ->helperText('O ícone correspondente é exibido automaticamente no site.'),

                Forms\Components\TextInput::make('label')
                    ->label('Rótulo (opcional)')
                    ->maxLength(100)
                    ->helperText('Se vazio, usa o nome padrão da plataforma.'),

                Forms\Components\TextInput::make('url')
                    ->label('URL completa')
                    ->required()
                    ->url()
                    ->maxLength(500)
                    ->placeholder('https://www.instagram.com/tmacimport')
                    ->columnSpanFull(),

                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Ordem de exibição')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Ativo')
                        ->default(true)
                        ->inline(false),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('platform')
                    ->label('Plataforma')
                    ->formatStateUsing(fn ($state) => SocialLink::PLATFORMS[$state] ?? ucfirst($state))
                    ->badge(),
                Tables\Columns\TextColumn::make('display_label')->label('Rótulo'),
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(50)
                    ->url(fn ($record) => $record->url, true)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('sort_order')->label('Ordem')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Ativo'),
                Tables\Filters\SelectFilter::make('platform')
                    ->label('Plataforma')
                    ->options(SocialLink::PLATFORMS),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSocialLinks::route('/'),
            'create' => Pages\CreateSocialLink::route('/create'),
            'edit' => Pages\EditSocialLink::route('/{record}/edit'),
        ];
    }
}
