<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\MotorcycleModel;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationGroup = 'Catálogo';

    protected static ?string $modelLabel = 'Produto';

    protected static ?string $pluralModelLabel = 'Produtos';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificação')->schema([
                Forms\Components\TextInput::make('name')->label('Nome')->required()->maxLength(200)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                Forms\Components\TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true)->maxLength(220),
                Forms\Components\TextInput::make('sku')->label('SKU/Código')->required()->unique(ignoreRecord: true)->maxLength(80),
                Forms\Components\Select::make('brand_id')
                    ->label('Marca (opcional)')
                    ->relationship('brand', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('— Sem marca'),
                Forms\Components\Select::make('tmac_brand_id')
                    ->label('Linha Universo TMAC (opcional)')
                    ->relationship('tmacBrand', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('— Não pertence a uma linha própria')
                    ->helperText('Marque se este produto faz parte de Corami, LBJ, Atrox, Motoled, etc.'),
                Forms\Components\Select::make('categories')->label('Categorias')
                    ->relationship('categories', 'name')->multiple()->searchable()->preload(),
                Forms\Components\TextInput::make('quote_price')
                    ->label('Preço de referência (R$)')
                    ->helperText('USO INTERNO. Nunca exibido ao cliente — usado apenas para calcular o mínimo de cotação por estado.')
                    ->numeric()
                    ->prefix('R$')
                    ->step('0.01')
                    ->minValue(0)
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Descrições')->schema([
                Forms\Components\Textarea::make('short_description')->label('Descrição curta')->rows(2)->maxLength(500),
                Forms\Components\RichEditor::make('description')->label('Descrição completa')->columnSpanFull(),
                Forms\Components\KeyValue::make('specifications')->label('Características técnicas')
                    ->keyLabel('Característica')->valueLabel('Valor')->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Mídia')->schema([
                Forms\Components\FileUpload::make('main_image')->label('Imagem principal')->image()
                    ->directory('products')->imageEditor()->required(),
                Forms\Components\Repeater::make('images')->label('Galeria')->relationship('images')->schema([
                    Forms\Components\FileUpload::make('path')->image()->directory('products/gallery')->required(),
                    Forms\Components\TextInput::make('alt')->label('Texto alternativo')->maxLength(200),
                    Forms\Components\TextInput::make('sort_order')->label('Ordem')->numeric()->default(0),
                ])->columns(3)->reorderable()->collapsed()->columnSpanFull(),
            ]),

            Forms\Components\Section::make('Aplicações / Motos compatíveis')
                ->description('Liste todas as motos em que esta peça se encaixa. Use o intervalo de anos quando necessário.')
                ->schema([
                    Forms\Components\Repeater::make('fitments')
                        ->label('Compatibilidade')
                        ->relationship('fitments')
                        ->schema([
                            Forms\Components\Select::make('motorcycle_model_id')
                                ->label('Moto')
                                ->options(fn () => MotorcycleModel::with('make')->active()->get()
                                    ->mapWithKeys(fn ($m) => [$m->id => ($m->make?->name.' '.$m->name)])
                                    ->sort()
                                    ->toArray())
                                ->searchable()->required()->columnSpan(2),
                            Forms\Components\TextInput::make('year_from')->label('De (ano)')
                                ->numeric()->minValue(1950)->maxValue((int) date('Y') + 1)
                                ->placeholder('opcional'),
                            Forms\Components\TextInput::make('year_to')->label('Até (ano)')
                                ->numeric()->minValue(1950)->maxValue((int) date('Y') + 1)
                                ->placeholder('opcional'),
                            Forms\Components\TextInput::make('notes')->label('Observação')
                                ->maxLength(255)->columnSpanFull()
                                ->placeholder('Ex: apenas versão ESDi, exceto Flex, etc.'),
                        ])
                        ->columns(4)
                        ->reorderable()
                        ->collapsed()
                        ->itemLabel(fn (array $state): ?string => isset($state['motorcycle_model_id'])
                            ? (MotorcycleModel::find($state['motorcycle_model_id'])?->full_name ?? 'Moto')
                                .(($state['year_from'] ?? null) ? ' · '.$state['year_from'].(($state['year_to'] ?? null) ? '–'.$state['year_to'] : '–atual') : '')
                            : 'Nova aplicação')
                        ->addActionLabel('Adicionar moto compatível')
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Exibição & SEO')->schema([
                Forms\Components\Toggle::make('is_active')->label('Ativo')->default(true),
                Forms\Components\Toggle::make('is_featured')->label('Em destaque')->default(false),
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
                Tables\Columns\ImageColumn::make('main_image')->label('Img'),
                Tables\Columns\TextColumn::make('sku')->label('SKU')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('name')->label('Nome')->searchable()->limit(50),
                Tables\Columns\TextColumn::make('brand.name')->label('Marca')->sortable(),
                Tables\Columns\IconColumn::make('is_featured')->label('Destaque')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('brand_id')->relationship('brand', 'name')->label('Marca'),
                Tables\Filters\SelectFilter::make('tmac_brand_id')->relationship('tmacBrand', 'name')->label('Linha TMAC'),
                Tables\Filters\TernaryFilter::make('is_active')->label('Ativo'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Em destaque'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
