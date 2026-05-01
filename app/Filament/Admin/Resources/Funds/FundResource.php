<?php

namespace App\Filament\Admin\Resources\Funds;

use App\Filament\Admin\Resources\Funds\Pages\ManageFunds;
use App\Models\Fund;
use BackedEnum;
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

class FundResource extends Resource
{
    protected static ?string $model = Fund::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

   public static function form(Schema $schema): Schema
{
    return $schema
        ->components([
            TextInput::make('name')
                ->label('Fund Name')
                ->required()
                ->maxLength(255),

            TextInput::make('code')
                ->label('Fund Code')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50),

            TextInput::make('opening_balance')
                ->label('Opening Balance')
                ->numeric()
                ->default(0)
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    $set('current_balance', $state);
                    $set('available_balance', $state);
                }),

            TextInput::make('current_balance')
                ->label('Current Balance')
                ->numeric()
                ->disabled()
                ->dehydrated(),

            TextInput::make('available_balance')
                ->label('Available Balance')
                ->numeric()
                ->disabled()
                ->dehydrated(),
            TextInput::make('tax_rate')
                 ->label('Tax Rate (%)')
                 ->numeric()
                ->default(10)
                ->required(),
        ]);
}

   public static function table(Table $table): Table
{
    return $table
        ->recordTitleAttribute('name')
        ->columns([
            TextColumn::make('name')
                ->label('Fund Name')
                ->searchable()
                ->sortable(),

            TextColumn::make('code')
                ->label('Fund Code')
                ->searchable()
                ->sortable(),

            TextColumn::make('opening_balance')
                ->label('Opening Balance')
                ->money('BDT')
                ->sortable(),

            TextColumn::make('current_balance')
                ->label('Current Balance')
                ->money('BDT')
                ->sortable(),

            TextColumn::make('available_balance')
                ->label('Available Balance')
                ->money('BDT')
                ->sortable(),

            TextColumn::make('created_at')
                ->label('Created Date')
                ->dateTime()
                ->sortable(),
        ])
        ->filters([
            //
        ])
        ->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ])
        ->toolbarActions([
            BulkActionGroup::make([
                DeleteBulkAction::make(),
            ]),
        ]);
}
    public static function getPages(): array
    {
        return [
            'index' => ManageFunds::route('/'),
        ];
    }
}
