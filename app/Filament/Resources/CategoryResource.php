<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Catálogo';

    protected static ?string $modelLabel = 'Categoria';

    protected static ?string $pluralModelLabel = 'Categorias';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Dados básicos')->schema([
                Forms\Components\Select::make('parent_id')
                    ->label('Categoria pai')
                    ->options(fn ($record) => Category::query()
                        ->root()
                        ->when($record, fn ($q) => $q->where('id', '!=', $record->id))
                        ->orderBy('name')
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->placeholder('— Categoria raiz (sem pai)')
                    ->helperText('Deixe vazio para criar uma categoria principal. Selecione um pai para criar uma subcategoria.'),

                Forms\Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->maxLength(150)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', \Illuminate\Support\Str::slug($state))),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(180),

                Forms\Components\Textarea::make('description')->label('Descrição')->rows(3),

                Forms\Components\FileUpload::make('image_path')
                    ->label('Imagem')
                    ->image()
                    ->directory('categories')
                    ->imageEditor(),
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
                Tables\Columns\ImageColumn::make('image_path')->label('Img'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state, $record) => $record->parent_id ? '↳ ' . $state : $state)
                    ->description(fn ($record) => $record->parent?->name ? 'em: ' . $record->parent->name : null),
                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Pai')
                    ->badge()
                    ->placeholder('— raiz')
                    ->color('gray'),
                Tables\Columns\TextColumn::make('children_count')
                    ->label('Subs')
                    ->counts('children')
                    ->badge(),
                Tables\Columns\TextColumn::make('sort_order')->label('Ordem')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Ativo'),
                Tables\Filters\Filter::make('only_roots')
                    ->label('Só categorias-raiz')
                    ->query(fn ($q) => $q->whereNull('parent_id')),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
