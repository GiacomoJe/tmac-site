<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';

    protected static ?string $navigationGroup = 'Conteúdo';

    protected static ?string $modelLabel = 'Depoimento';

    protected static ?string $pluralModelLabel = 'Depoimentos';

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nome do cliente')
                    ->required()
                    ->maxLength(120),
                Forms\Components\TextInput::make('role')
                    ->label('Empresa / cidade / cargo')
                    ->maxLength(160)
                    ->placeholder('Ex: Auto Peças Silva — SP'),
                Forms\Components\Textarea::make('quote')
                    ->label('Depoimento')
                    ->rows(4)
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Select::make('rating')
                        ->label('Estrelas')
                        ->options([5 => '★★★★★ (5)', 4 => '★★★★☆ (4)', 3 => '★★★☆☆ (3)', 2 => '★★☆☆☆ (2)', 1 => '★☆☆☆☆ (1)'])
                        ->default(5)
                        ->required(),
                    Forms\Components\TextInput::make('sort_order')
                        ->label('Ordem')
                        ->numeric()
                        ->default(0),
                    Forms\Components\Toggle::make('is_active')
                        ->label('Ativo')
                        ->default(true)
                        ->inline(false),
                ]),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Cliente')->searchable(),
                Tables\Columns\TextColumn::make('role')->label('Empresa')->searchable(),
                Tables\Columns\TextColumn::make('quote')->label('Depoimento')->limit(60),
                Tables\Columns\TextColumn::make('rating')->label('★')
                    ->formatStateUsing(fn ($state) => str_repeat('★', (int) $state) . str_repeat('☆', 5 - (int) $state)),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make(), Tables\Actions\DeleteAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit'   => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
