<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTransactions extends BaseWidget
{
    protected static ?string $heading = 'Recent Transactions';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Transaction::with('fdr')->latest()
            )
            ->defaultPaginationPageOption(5)
            ->columns([

                // ✅ FDR No from fdr table
                Tables\Columns\TextColumn::make('fdr.fdr_account_number')
                    ->label('FDR No')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state ?? 'N/A'),

                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'success' => 'deposit',
                        'danger' => 'withdraw',
                        'info' => 'interest',
                        'warning' => 'tax',
                    ]),

                Tables\Columns\TextColumn::make('net_amount')
                    ->money('BDT', true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d M Y, h:i A'),
            ]);
    }
}