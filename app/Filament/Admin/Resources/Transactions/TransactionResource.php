<?php

namespace App\Filament\Admin\Resources\Transactions;

use App\Models\Transaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    // 🚫 DISABLE CREATE
    public static function canCreate(): bool
    {
        return false;
    }

    // 🚫 DISABLE EDIT
    public static function canEdit($record): bool
    {
        return false;
    }

    // 🚫 DISABLE DELETE
    public static function canDelete($record): bool
    {
        return false;
    }

    // 🚫 NO FORM (READ ONLY LEDGER)
    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('fdr.fdr_number')
                    ->label('FDR No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'create' => 'primary',
                        'renew' => 'warning',
                        'encash' => 'danger',
                        'interest' => 'success',
                        'tax' => 'gray',
                        'duty' => 'info',
                        default => 'secondary',
                    })
                    ->sortable(),

                TextColumn::make('principal')
                    ->money('BDT')
                    ->sortable(),

                TextColumn::make('interest')
                    ->money('BDT')
                    ->sortable(),

                TextColumn::make('tax')
                    ->money('BDT')
                    ->sortable(),

                TextColumn::make('duty')
                    ->money('BDT')
                    ->sortable(),

                TextColumn::make('net_amount')
                    ->label('Net Amount')
                    ->money('BDT')
                    ->sortable(),

                TextColumn::make('transaction_date')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('remarks')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'create' => 'Create',
                        'renew' => 'Renew',
                        'encash' => 'Encash',
                        'interest' => 'Interest',
                        'tax' => 'Tax',
                        'duty' => 'Duty',
                    ]),

                SelectFilter::make('fdr')
                    ->relationship('fdr', 'fdr_number'),
            ])
            // 🚫 NO ACTIONS
            ->actions([])
            // 🚫 NO BULK ACTIONS
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageTransactions::route('/'),
        ];
    }
}