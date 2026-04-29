<?php

namespace App\Filament\Admin\Resources\Banks;

use App\Filament\Admin\Resources\Banks\Pages\ManageBanks;
use App\Models\Bank;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BankResource extends Resource
{
    protected static ?string $model = Bank::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

   public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Bank Name')
                ->required()
                ->maxLength(255),

            Select::make('type')
                ->label('Bank Type')
                ->options([
                    'govt' => 'Government',
                    'private' => 'Private',
                ])
                ->required(),

            TextInput::make('branch_name')
                ->label('Branch Name'),

            TextInput::make('account_number')
                ->label('Account Number'),

            TextInput::make('routing_number')
                ->label('Routing Number'),

            TextInput::make('phone')
                ->label('Phone'),

            TextInput::make('address')
                ->label('Address'),

            TextInput::make('total_investment')
                ->label('Total Investment')
                ->numeric()
                ->default(0)
                ->disabled()
                ->dehydrated(),
        ]);
    }

    // 📊 TABLE
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn ($state) => $state === 'govt' ? 'success' : 'warning'),

                TextColumn::make('branch_name'),

                TextColumn::make('account_number'),

                TextColumn::make('total_investment')
                    ->money('BDT')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageBanks::route('/'),
        ];
    }
}
