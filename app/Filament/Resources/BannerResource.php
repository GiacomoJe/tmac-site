<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Conteúdo';

    protected static ?string $modelLabel = 'Banner';

    protected static ?string $pluralModelLabel = 'Banners';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Imagens')->schema([
                Forms\Components\FileUpload::make('image_mobile')->label('Imagem mobile (4:5)')->image()->directory('banners')->imageEditor(),
                Forms\Components\FileUpload::make('image_desktop')->label('Imagem desktop (16:6)')->image()->directory('banners')->imageEditor(),
            ])->columns(2),

            Forms\Components\Section::make('Modo de exibição')->schema([
                Forms\Components\Toggle::make('image_only')
                    ->label('Banner limpo (só imagem)')
                    ->helperText('Quando ativado: NÃO sobrepõe título, subtítulo, badges, gradiente nem botão. Útil quando a arte já tem tudo embutido.')
                    ->inline(false)
                    ->default(false)
                    ->live(),
            ]),

            Forms\Components\Section::make('Texto e botão')
                ->schema([
                    Forms\Components\TextInput::make('title')->label('Título')->maxLength(180),
                    Forms\Components\TextInput::make('subtitle')->label('Subtítulo')->maxLength(250),
                    Forms\Components\TextInput::make('cta_label')->label('Texto do botão')->maxLength(60),
                    Forms\Components\TextInput::make('link_url')->label('URL do botão')->maxLength(255),
                ])
                ->columns(2)
                ->hidden(fn (Forms\Get $get) => $get('image_only'))
                ->collapsible(),

            Forms\Components\Section::make('Link (banner limpo)')
                ->schema([
                    Forms\Components\TextInput::make('link_url')->label('URL ao clicar (opcional)')->maxLength(255),
                ])
                ->visible(fn (Forms\Get $get) => $get('image_only'))
                ->collapsible(),

            Forms\Components\Section::make('Exibição')->schema([
                Forms\Components\Select::make('position')->label('Posição')->options([
                    'home_top' => 'Home — Topo',
                    'home_mid' => 'Home — Meio',
                ])->default('home_top')->required(),
                Forms\Components\TextInput::make('sort_order')->label('Ordem')->numeric()->default(0),
                Forms\Components\DateTimePicker::make('starts_at')->label('Inicia em'),
                Forms\Components\DateTimePicker::make('ends_at')->label('Termina em'),
                Forms\Components\Toggle::make('is_active')->label('Ativo')->default(true)->inline(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_mobile')->label('Mobile'),
                Tables\Columns\TextColumn::make('title')->label('Título')->limit(30)->placeholder('—'),
                Tables\Columns\TextColumn::make('position')->label('Posição')->badge(),
                Tables\Columns\IconColumn::make('image_only')
                    ->label('Limpo')
                    ->boolean()
                    ->trueIcon('heroicon-o-photo')
                    ->falseIcon('heroicon-o-document-text')
                    ->trueColor('info')
                    ->falseColor('gray'),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit' => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
