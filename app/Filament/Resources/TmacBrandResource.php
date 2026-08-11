<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TmacBrandResource\Pages;
use App\Models\TmacBrand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TmacBrandResource extends Resource
{
    protected static ?string $model = TmacBrand::class;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'Conteúdo';

    protected static ?string $modelLabel = 'Linha Universo TMAC';

    protected static ?string $pluralModelLabel = 'Universo TMAC';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificação')->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Nome da linha')
                        ->required()
                        ->maxLength(120)
                        ->placeholder('Ex: TMAC Motor'),
                    Forms\Components\TextInput::make('slug')
                        ->label('Slug')
                        ->maxLength(140)
                        ->helperText('Deixe em branco para gerar automaticamente.'),
                ]),
                Forms\Components\TextInput::make('badge_label')
                    ->label('Etiqueta curta')
                    ->maxLength(60)
                    ->placeholder('Ex: Motor, Premium, Acessórios')
                    ->helperText('Aparece nos cards do site.'),
                Forms\Components\TextInput::make('tagline')
                    ->label('Chamada')
                    ->maxLength(160)
                    ->placeholder('Ex: O melhor em cada detalhe')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('description')
                    ->label('Descrição completa')
                    ->rows(4)
                    ->maxLength(1000)
                    ->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Visual')->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\FileUpload::make('image_path')
                        ->label('Foto principal')
                        ->image()
                        ->disk('public')
                        ->directory('tmac-brands')
                        ->helperText('Foto editorial da linha (recomendado: 1200×800px).'),
                    Forms\Components\FileUpload::make('logo_path')
                        ->label('Logo (opcional)')
                        ->image()
                        ->disk('public')
                        ->directory('tmac-brands/logos')
                        ->helperText('Logo da linha em PNG transparente.'),
                ]),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\ColorPicker::make('brand_color')
                        ->label('Cor sólida da marca')
                        ->helperText('Hex (ex: #E97B0B). Aparece como fundo do card. Vazio → usa accent.'),
                    Forms\Components\Select::make('text_theme')
                        ->label('Tema de texto sobre a cor')
                        ->options(['light' => 'Texto branco', 'dark' => 'Texto preto'])
                        ->default('light')
                        ->required()
                        ->helperText('Escolha o contraste ideal sobre a cor.'),
                    Forms\Components\Select::make('accent_color')
                        ->label('Tom secundário (fallback)')
                        ->options(TmacBrand::ACCENTS)
                        ->default('signal')
                        ->required()
                        ->helperText('Usado quando "Cor sólida" estiver vazia.'),
                ]),
                Forms\Components\TextInput::make('link_url')
                    ->label('Link opcional')
                    ->url()
                    ->maxLength(255)
                    ->helperText('URL externa ou interna. Vazio → vai para /universo-tmac/{slug}.'),
            ])->collapsed(),

            Forms\Components\Section::make('SEO')->schema([
                Forms\Components\TextInput::make('meta_title')->label('Título SEO')->maxLength(160),
                Forms\Components\Textarea::make('meta_description')->label('Descrição SEO')->maxLength(255)->rows(2),
            ])->collapsed(),

            Forms\Components\Section::make('Exibição')->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\TextInput::make('sort_order')->label('Ordem')->numeric()->default(0),
                    Forms\Components\Toggle::make('is_active')->label('Ativo')->default(true),
                    Forms\Components\Toggle::make('is_featured')->label('Destacar na home')->default(false),
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
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('')
                    ->disk('public')
                    ->square()
                    ->size(50),
                Tables\Columns\TextColumn::make('name')
                    ->label('Linha')
                    ->searchable()
                    ->description(fn ($record) => $record->tagline),
                Tables\Columns\TextColumn::make('badge_label')->label('Etiqueta')->badge(),
                Tables\Columns\TextColumn::make('accent_color')
                    ->label('Cor')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'accent' => 'danger',
                        'signal' => 'info',
                        'whatsapp' => 'success',
                        default => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_featured')->label('Home')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Ativo'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Destacado'),
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
            'index'  => Pages\ListTmacBrands::route('/'),
            'create' => Pages\CreateTmacBrand::route('/create'),
            'edit'   => Pages\EditTmacBrand::route('/{record}/edit'),
        ];
    }
}
