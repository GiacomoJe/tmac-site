<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesOrderResource\Pages;
use App\Models\SalesOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SalesOrderResource extends Resource
{
    protected static ?string $model = SalesOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Tabela de Vendas';

    protected static ?string $navigationLabel = 'Pedidos';

    protected static ?string $modelLabel = 'Pedido';

    protected static ?string $pluralModelLabel = 'Pedidos';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'code';

    private const STATUS_LABELS = [
        'novo' => 'Novo',
        'em_atendimento' => 'Em atendimento',
        'faturado' => 'Faturado',
        'cancelado' => 'Cancelado',
    ];

    private const STATUS_COLORS = [
        'novo' => 'warning',
        'em_atendimento' => 'info',
        'faturado' => 'success',
        'cancelado' => 'danger',
    ];

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::where('status', 'novo')->count();
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificação')->schema([
                Forms\Components\TextInput::make('code')->label('Código')->disabled(),
                Forms\Components\TextInput::make('tabela')->label('Tabela')->disabled(),
                Forms\Components\Select::make('status')->label('Status')->options(self::STATUS_LABELS)->required(),
            ])->columns(3),

            Forms\Components\Section::make('Cliente / pedido')->schema([
                Forms\Components\TextInput::make('razao_social')->label('Razão social')->disabled(),
                Forms\Components\TextInput::make('cnpj')->label('CNPJ')->disabled(),
                Forms\Components\TextInput::make('responsavel')->label('Responsável')->disabled(),
                Forms\Components\TextInput::make('telefone')->label('Telefone')->disabled(),
                Forms\Components\TextInput::make('email')->label('E-mail')->disabled(),
                Forms\Components\TextInput::make('vendedor')->label('Vendedor')->disabled(),
                Forms\Components\TextInput::make('transportadora')->label('Transportadora')->disabled(),
                Forms\Components\TextInput::make('cnpj_transportadora')->label('CNPJ transportadora')->disabled(),
                Forms\Components\TextInput::make('prazo_pagamento')->label('Prazo de pagamento')->disabled(),
                Forms\Components\Textarea::make('observacoes')->label('Observações')->rows(2)->disabled()->columnSpanFull(),
            ])->columns(3),

            // Só o "status" acima é realmente editável — os itens ficam só pra
            // conferência aqui na tela de edição (a lista compacta, sem precisar
            // expandir, fica na tela de visualização — ver infolist() abaixo).
            Forms\Components\Section::make('Itens')->schema([
                Forms\Components\Repeater::make('items')->relationship('items')->label('Itens do pedido')
                    ->schema([
                        Forms\Components\TextInput::make('cod')->label('Cód.')->disabled(),
                        Forms\Components\TextInput::make('descricao')->label('Descrição')->disabled()->columnSpan(2),
                        Forms\Components\TextInput::make('quantidade')->label('Qtd')->disabled(),
                        Forms\Components\TextInput::make('desconto_percent')->label('Desc. %')->disabled(),
                        Forms\Components\TextInput::make('valor_final')->label('Valor final')->disabled(),
                        Forms\Components\TextInput::make('total')->label('Total')->disabled(),
                        Forms\Components\TextInput::make('bloqueado')->label('Estoque')->disabled()
                            ->formatStateUsing(fn (?bool $state) => $state ? 'Sem estoque' : 'OK'),
                    ])->columns(7)->disabled()
                    ->deletable(false)->addable(false)->reorderable(false),
            ]),

            Forms\Components\Section::make('Totais')->schema([
                Forms\Components\TextInput::make('total')->label('Total do pedido')->disabled()->prefix('R$'),
                Forms\Components\TextInput::make('total_bloqueado')->label('Fora do total (sem estoque)')->disabled()->prefix('R$'),
            ])->columns(2),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Identificação')->schema([
                Infolists\Components\TextEntry::make('code')->label('Código')->copyable(),
                Infolists\Components\TextEntry::make('tabela')->label('Tabela')->badge(),
                Infolists\Components\TextEntry::make('status')->label('Status')->badge()
                    ->formatStateUsing(fn (string $state) => self::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state) => self::STATUS_COLORS[$state] ?? 'gray'),
                Infolists\Components\TextEntry::make('created_at')->label('Enviado em')->dateTime('d/m/Y H:i'),
            ])->columns(4),

            Infolists\Components\Section::make('Cliente / pedido')->schema([
                Infolists\Components\TextEntry::make('razao_social')->label('Razão social'),
                Infolists\Components\TextEntry::make('cnpj')->label('CNPJ')->placeholder('—'),
                Infolists\Components\TextEntry::make('responsavel')->label('Responsável'),
                Infolists\Components\TextEntry::make('telefone')->label('Telefone'),
                Infolists\Components\TextEntry::make('email')->label('E-mail'),
                Infolists\Components\TextEntry::make('vendedor')->label('Vendedor')->placeholder('—'),
                Infolists\Components\TextEntry::make('transportadora')->label('Transportadora')->placeholder('—'),
                Infolists\Components\TextEntry::make('cnpj_transportadora')->label('CNPJ transportadora')->placeholder('—'),
                Infolists\Components\TextEntry::make('prazo_pagamento')->label('Prazo de pagamento')->placeholder('—'),
                Infolists\Components\TextEntry::make('observacoes')->label('Observações')->placeholder('—')->columnSpanFull(),
            ])->columns(3),

            // Lista compacta dos itens — visível direto, sem precisar expandir
            // (é um Infolist, não um Repeater de formulário: sem botões de
            // adicionar/remover/arrastar, só os valores, ocupando bem menos espaço).
            // Cada item fica numa "caixinha" (contained, o padrão) só pra separar
            // visualmente uma linha da outra — e mostra se o item estava sem
            // estoque no momento em que o pedido foi salvo.
            Infolists\Components\Section::make('Itens do pedido')->schema([
                Infolists\Components\RepeatableEntry::make('items')->hiddenLabel()
                    ->schema([
                        Infolists\Components\TextEntry::make('cod')->label('Cód.'),
                        Infolists\Components\TextEntry::make('descricao')->label('Descrição')->columnSpan(2),
                        Infolists\Components\TextEntry::make('quantidade')->label('Qtd'),
                        Infolists\Components\TextEntry::make('desconto_percent')->label('Desc. %')->suffix('%'),
                        Infolists\Components\TextEntry::make('valor_final')->label('Valor final')->money('BRL'),
                        Infolists\Components\TextEntry::make('total')->label('Total')->money('BRL'),
                        Infolists\Components\TextEntry::make('bloqueado')->label('Estoque')->badge()
                            ->formatStateUsing(fn (bool $state) => $state ? 'Sem estoque' : 'OK')
                            ->color(fn (bool $state) => $state ? 'danger' : 'success'),
                    ])->columns(8),
            ]),

            Infolists\Components\Section::make('Totais')->schema([
                Infolists\Components\TextEntry::make('total')->label('Total do pedido')->money('BRL'),
                Infolists\Components\TextEntry::make('total_bloqueado')->label('Fora do total (sem estoque)')->money('BRL'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('Código')->searchable()->copyable(),
                Tables\Columns\TextColumn::make('cliente.name')->label('Cliente')->searchable(),
                Tables\Columns\TextColumn::make('razao_social')->label('Empresa')->searchable()->toggleable(),
                Tables\Columns\TextColumn::make('tabela')->label('Tabela')->badge(),
                Tables\Columns\TextColumn::make('itens_count')->label('Itens'),
                Tables\Columns\TextColumn::make('total')->label('Total')->money('BRL')->sortable(),
                Tables\Columns\BadgeColumn::make('status')->label('Status')->colors(array_flip(self::STATUS_COLORS)),
                Tables\Columns\TextColumn::make('created_at')->label('Enviado em')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(self::STATUS_LABELS),
                Tables\Filters\SelectFilter::make('tabela')->label('Tabela')->options(
                    fn () => SalesOrder::query()->distinct()->pluck('tabela', 'tabela')->all()
                ),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesOrders::route('/'),
            'view' => Pages\ViewSalesOrder::route('/{record}'),
            'edit' => Pages\EditSalesOrder::route('/{record}/edit'),
        ];
    }
}
