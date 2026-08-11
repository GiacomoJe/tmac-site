<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BrandResource\Pages;
use App\Models\Brand;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Catálogo';

    protected static ?string $modelLabel = 'Marca';

    protected static ?string $pluralModelLabel = 'Marcas';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados básicos')->schema([
                Forms\Components\TextInput::make('name')->label('Nome')->required()->maxLength(150)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                Forms\Components\TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true)->maxLength(180),
                Forms\Components\Textarea::make('description')->label('Descrição')->rows(3),
                Forms\Components\FileUpload::make('logo_path')->label('Logo')->image()->directory('brands')->imageEditor(),
                Forms\Components\TextInput::make('website_url')->label('Site')->url()->maxLength(255),
            ])->columns(2),

            Forms\Components\Section::make('Exibição & SEO')->schema([
                Forms\Components\Toggle::make('is_active')->label('Ativo')->default(true),
                Forms\Components\TextInput::make('sort_order')->label('Ordem')->numeric()->default(0),
                Forms\Components\TextInput::make('seo_title')->label('SEO Title')->maxLength(180),
                Forms\Components\Textarea::make('seo_description')->label('SEO Description')->rows(2)->maxLength(255),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo_path')->label('Logo'),
                Tables\Columns\TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('products_count')->counts('products')->label('Produtos'),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([Tables\Filters\TernaryFilter::make('is_active')->label('Ativo')])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
