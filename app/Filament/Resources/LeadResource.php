<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadResource\Pages;
use App\Models\Lead;
use App\Models\State;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LeadResource extends Resource
{
    protected static ?string $model = Lead::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox-arrow-down';

    protected static ?string $navigationGroup = 'Comercial';

    protected static ?string $modelLabel = 'Lead';

    protected static ?string $pluralModelLabel = 'Leads';

    protected static ?int $navigationSort = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'new')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identificação')->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\TextInput::make('name')->label('Nome')->required()->maxLength(150),
                    Forms\Components\TextInput::make('email')->label('E-mail')->email()->required()->maxLength(150),
                    Forms\Components\TextInput::make('phone')->label('Telefone')->maxLength(30),
                    Forms\Components\TextInput::make('company')->label('Empresa')->maxLength(150),
                ]),
            ]),

            Forms\Components\Section::make('Localização')->schema([
                Forms\Components\Grid::make(3)->schema([
                    Forms\Components\Select::make('state_id')
                        ->label('Estado')
                        ->options(State::orderBy('name')->pluck('name', 'id'))
                        ->searchable(),
                    Forms\Components\TextInput::make('city')->label('Cidade')->maxLength(120),
                ]),
            ]),

            Forms\Components\Section::make('Mensagem')->schema([
                Forms\Components\TextInput::make('subject')->label('Assunto')->maxLength(120),
                Forms\Components\TextInput::make('interest')->label('Interesse')->maxLength(80),
                Forms\Components\Textarea::make('message')->label('Mensagem')->rows(4)->maxLength(2000),
            ]),

            Forms\Components\Section::make('Origem')->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('source')
                        ->label('Origem')
                        ->options(Lead::SOURCES)
                        ->required(),
                    Forms\Components\TextInput::make('source_url')->label('URL de origem')->maxLength(255),
                ]),
            ])->collapsed(),

            Forms\Components\Section::make('Gestão')->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->options(Lead::STATUSES)
                        ->required()
                        ->default('new'),
                    Forms\Components\DateTimePicker::make('contacted_at')->label('Contatado em'),
                ]),
                Forms\Components\Textarea::make('admin_notes')->label('Notas internas')->rows(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Data')
                    ->dateTime('d/m H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->limit(30)
                    ->description(fn ($record) => $record->company),

                Tables\Columns\TextColumn::make('email')
                    ->label('Contato')
                    ->searchable()
                    ->description(fn ($record) => $record->phone),

                Tables\Columns\TextColumn::make('source')
                    ->label('Origem')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        Lead::SOURCE_CONTACT    => 'primary',
                        Lead::SOURCE_REPRESENT  => 'success',
                        Lead::SOURCE_NEWSLETTER => 'info',
                        Lead::SOURCE_CATALOG    => 'warning',
                        default                 => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => Lead::SOURCES[$state] ?? $state),

                Tables\Columns\TextColumn::make('state.uf')->label('UF')->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'new'        => 'warning',
                        'contacted'  => 'info',
                        'qualified'  => 'success',
                        'converted'  => 'success',
                        'discarded'  => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => Lead::STATUSES[$state] ?? $state),

                Tables\Columns\IconColumn::make('rd_synced_at')
                    ->label('RD')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->getStateUsing(fn ($record) => $record->rd_synced_at !== null),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options(Lead::STATUSES),
                Tables\Filters\SelectFilter::make('source')
                    ->label('Origem')
                    ->options(Lead::SOURCES),
                Tables\Filters\Filter::make('not_contacted')
                    ->label('Sem contato ainda')
                    ->query(fn ($q) => $q->whereNull('contacted_at')),
            ])
            ->actions([
                Tables\Actions\Action::make('mark_contacted')
                    ->label('Contatar')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'new')
                    ->action(function ($record) {
                        $record->update(['status' => 'contacted', 'contacted_at' => now()]);
                    }),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([Tables\Actions\DeleteBulkAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLeads::route('/'),
            'create' => Pages\CreateLead::route('/create'),
            'edit'   => Pages\EditLead::route('/{record}/edit'),
        ];
    }
}
