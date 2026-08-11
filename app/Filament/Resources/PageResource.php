<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PageResource\Pages;
use App\Models\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationGroup = 'Conteúdo';

    protected static ?string $modelLabel = 'Página';

    protected static ?string $pluralModelLabel = 'Páginas';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificação')->schema([
                Forms\Components\TextInput::make('title')->label('Título')->required()->maxLength(200),
                Forms\Components\TextInput::make('slug')->label('Slug')->required()->unique(ignoreRecord: true)->maxLength(180),
            ])->columns(2),

            Forms\Components\Section::make('Hero (topo da página)')
                ->description('Textos e imagem que aparecem no topo da página.')
                ->schema([
                    Forms\Components\FileUpload::make('hero_image')
                        ->label('Imagem de capa')
                        ->image()
                        ->imageEditor()
                        ->directory('pages')
                        ->disk('public')
                        ->helperText('Recomendado: 2400×1300px, JPG/WebP.')
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('hero_eyebrow')
                        ->label('Etiqueta (badge do topo)')
                        ->maxLength(120)
                        ->placeholder('Ex: Ao universo TMAC, Fale com a TMAC…'),

                    Forms\Components\TextInput::make('hero_title')
                        ->label('Título principal (H1)')
                        ->maxLength(200)
                        ->placeholder('Ex: Quem somos.'),

                    Forms\Components\TextInput::make('hero_highlight')
                        ->label('Palavra em destaque (vermelho/azul)')
                        ->maxLength(100)
                        ->helperText('Uma palavra ou expressão do título que fica colorida. Ex: em "Quem somos.", pode ser "somos".'),

                    Forms\Components\Textarea::make('hero_description')
                        ->label('Descrição do topo')
                        ->rows(3)
                        ->maxLength(500)
                        ->placeholder('Parágrafo curto que aparece abaixo do título.')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsible(),

            Forms\Components\Section::make('Regras "O que não fazemos" (só Quem somos)')
                ->description('Bloco de regras/transparência. Deixe vazio para usar os textos padrão do site.')
                ->schema([
                    Forms\Components\Repeater::make('extra_data.rules')
                        ->label('Regras')
                        ->schema([
                            Forms\Components\TextInput::make('title')->label('Título')->required()->maxLength(120),
                            Forms\Components\Textarea::make('sub')->label('Descrição')->rows(2)->required()->maxLength(255),
                        ])
                        ->columns(1)
                        ->collapsible()
                        ->cloneable()
                        ->reorderableWithButtons()
                        ->maxItems(6)
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Regra')
                        ->addActionLabel('+ Adicionar regra'),
                ])
                ->collapsed()
                ->visible(fn ($record) => $record && $record->slug === 'quem-somos'),

            Forms\Components\Section::make('Cards de orientação (só Ouvidoria)')
                ->description('3 cards no topo da Ouvidoria (Sugestão / Reclamação / Elogio).')
                ->schema([
                    Forms\Components\Repeater::make('extra_data.cards')
                        ->label('Cards')
                        ->schema([
                            Forms\Components\TextInput::make('title')->label('Título')->required()->maxLength(80),
                            Forms\Components\Textarea::make('description')->label('Descrição')->rows(2)->required()->maxLength(200),
                        ])
                        ->columns(1)
                        ->collapsible()
                        ->cloneable()
                        ->reorderableWithButtons()
                        ->maxItems(6)
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Card')
                        ->addActionLabel('+ Adicionar card'),
                ])
                ->collapsed()
                ->visible(fn ($record) => $record && $record->slug === 'ouvidoria'),

            Forms\Components\Section::make('Conteúdo livre (HTML)')
                ->description('Aparece em blocos que aceitam prose. Em páginas com layout customizado, sobrescreve texto padrão.')
                ->schema([
                    Forms\Components\RichEditor::make('content')
                        ->label('Conteúdo')
                        ->columnSpanFull(),
                ])
                ->collapsed(),

            Forms\Components\Section::make('SEO')->schema([
                Forms\Components\TextInput::make('seo_title')->label('SEO Title')->maxLength(180),
                Forms\Components\Textarea::make('seo_description')->label('SEO Description')->rows(2)->maxLength(255),
            ])->columns(1)->collapsed(),

            Forms\Components\Section::make('Publicação')->schema([
                Forms\Components\Toggle::make('is_active')->label('Ativo')->default(true)->inline(false),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('hero_image')->label('Capa')->disk('public')->square()->size(40),
                Tables\Columns\TextColumn::make('title')->label('Título')->searchable(),
                Tables\Columns\TextColumn::make('slug')->label('Slug')->copyable(),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
                Tables\Columns\TextColumn::make('updated_at')->label('Atualizado')->dateTime('d/m/Y H:i'),
            ])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit'   => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
