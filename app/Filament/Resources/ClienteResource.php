<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClienteResource\Pages;
use App\Models\Cliente;
use App\Models\SalesPriceList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class ClienteResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Tabela de Vendas';

    protected static ?string $navigationLabel = 'Clientes (acessos)';

    protected static ?string $modelLabel = 'Cliente';

    protected static ?string $pluralModelLabel = 'Clientes';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        // Tabelas (estados) disponíveis na versão publicada atual, para sugerir a tabela padrão do cliente.
        $tabelasDisponiveis = optional(SalesPriceList::active())
            ->states()
            ->pluck('code', 'code')
            ->all() ?? [];

        return $form->schema([
            Forms\Components\Section::make('Acesso')->schema([
                Forms\Components\TextInput::make('name')->label('Nome do contato')->required()->maxLength(150),
                Forms\Components\TextInput::make('email')->label('E-mail (login)')->email()->required()->unique(ignoreRecord: true)->maxLength(150),
                Forms\Components\TextInput::make('password')
                    ->label('Senha')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->required(fn (string $context) => $context === 'create')
                    ->helperText('Deixe em branco na edição para manter a senha atual.'),
                Forms\Components\Toggle::make('is_active')->label('Acesso ativo')->default(true),
            ])->columns(2),

            Forms\Components\Section::make('Empresa')->schema([
                Forms\Components\TextInput::make('company')->label('Razão social')->maxLength(150),
                Forms\Components\TextInput::make('cnpj')->label('CNPJ')->maxLength(20),
                Forms\Components\TextInput::make('phone')->label('Telefone')->maxLength(30),
                Forms\Components\TextInput::make('whatsapp')->label('WhatsApp')->maxLength(30),
                Forms\Components\TextInput::make('city')->label('Cidade')->maxLength(120),
                Forms\Components\Select::make('uf')->label('UF')
                    ->options(\App\Models\State::orderBy('name')->pluck('uf', 'uf'))
                    ->searchable(),
            ])->columns(2),

            Forms\Components\Section::make('Tabela de Vendas')->schema([
                Forms\Components\Select::make('tabela_padrao')
                    ->label('Tabela (estado de preço) padrão')
                    ->options($tabelasDisponiveis)
                    ->searchable()
                    ->helperText('Tabela pré-selecionada quando este cliente entra na Área do Cliente. Ele pode trocar depois.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Contato')->searchable(),
                Tables\Columns\TextColumn::make('company')->label('Empresa')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('E-mail')->searchable(),
                Tables\Columns\TextColumn::make('tabela_padrao')->label('Tabela')->badge(),
                Tables\Columns\TextColumn::make('last_login_at')->label('Último acesso')->dateTime('d/m/Y H:i')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Ativo')->boolean(),
            ])
            ->filters([Tables\Filters\TernaryFilter::make('is_active')->label('Ativo')])
            ->actions([Tables\Actions\EditAction::make()])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateCliente::route('/create'),
            'edit' => Pages\EditCliente::route('/{record}/edit'),
        ];
    }
}
