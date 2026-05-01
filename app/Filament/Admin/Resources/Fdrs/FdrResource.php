<?php

namespace App\Filament\Admin\Resources\Fdrs;

use App\Filament\Admin\Resources\Fdrs\Pages\ManageFdrs;
use App\Models\Fdr;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Actions\Action;

use Carbon\Carbon;

class FdrResource extends Resource
{
    protected static ?string $model = Fdr::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'fdr_number';

   public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('fdr_number')->required(),

            Select::make('fund_id')
                ->relationship('fund', 'name')
                ->required(),
            TextInput::make('fdr_account_number')
            ->label('FDR Account Number')
            ->required(),
           
      Select::make('bank_id')
    ->label('Bank')
    ->searchable()
    ->preload()

    ->getSearchResultsUsing(function (string $search) {
        return \App\Models\Bank::query()
            ->where('name', 'like', "%{$search}%")
            ->orWhere('branch_name', 'like', "%{$search}%")
            ->limit(50)
            ->get()
            ->mapWithKeys(fn ($bank) => [
                $bank->id => $bank->name . ' - ' . $bank->branch_name
            ])
            ->toArray();
    })

    ->getOptionLabelUsing(function ($value) {
        $bank = \App\Models\Bank::find($value);

        return $bank
            ? $bank->name . ' - ' . $bank->branch_name
            : null;
    })

    ->live()
    ->afterStateUpdated(function ($state, callable $set) {

        $bank = \App\Models\Bank::find($state);

        $set('branch_name', $bank?->branch_name);
    }),
    TextInput::make('branch_name')
    ->label('Branch Name')
    ->readOnly()
    ->dehydrated(true),// IMPORTANT (so it stores if needed)

            TextInput::make('amount')
                ->numeric()
                ->required(),
            TextInput::make('charge')
                ->numeric()
                ->required(),

            TextInput::make('interest_rate')
                ->numeric()
                ->required(),

            TextInput::make('tenure')
                ->label('Tenure (Months)')
                ->numeric()
                ->required(),

            DatePicker::make('start_date')
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, $get, $set) {
                    $set(
                        'maturity_date',
                        \Carbon\Carbon::parse($state)->addMonths($get('tenure'))
                    );
                }),

            DatePicker::make('maturity_date')
                ->disabled()
                ->dehydrated(),
        ]);
    }


    public static function table(Table $table): Table
{
    return $table
        ->recordTitleAttribute('fdr_number')
        ->columns([
            TextColumn::make('fdr_number')
                ->label('FDR No')
                ->searchable()
                ->sortable(),

            TextColumn::make('fund.name')
                ->label('Fund')
                ->searchable()
                ->sortable(),

            TextColumn::make('bank.name')
                ->label('Bank')
                ->searchable()
                ->sortable(),

            TextColumn::make('amount')
                ->label('Investment')
                ->money('BDT')
                ->sortable(),

            TextColumn::make('interest_rate')
                ->label('Rate (%)')
                ->suffix('%')
                ->sortable(),

            TextColumn::make('tenure')
                ->label('Tenure')
                ->suffix(' Months'),

            TextColumn::make('start_date')
                ->label('Start Date')
                ->date()
                ->sortable(),

            TextColumn::make('maturity_date')
                ->label('Maturity Date')
                ->date()
                ->sortable()
                ->color(fn ($state) => now()->diffInDays($state, false) <= 30 ? 'danger' : null),

            TextColumn::make('status')
                ->badge()
                ->color(fn ($state) => match ($state) {
                    'active' => 'success',
                    'renewed' => 'warning',
                    'encashed' => 'danger',
                }),

            TextColumn::make('created_at')
                ->dateTime()
                ->sortable(),
        ])
        ->filters([
            SelectFilter::make('status')
                ->options([
                    'active' => 'Active',
                    'renewed' => 'Renewed',
                    'encashed' => 'Encashed',
                ]),

            SelectFilter::make('bank')
                ->relationship('bank', 'name'),

            SelectFilter::make('fund')
                ->relationship('fund', 'name'),
        ])
        ->actions([
            EditAction::make(),

            Action::make('renew')
                ->label('Renew')
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn ($record) => $record->status === 'active')
                ->action(fn ($record) => \App\Services\FdrService::renew($record)),

            Action::make('renewNet')
                ->label('Renew (Net)')
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'active')
                ->action(fn ($record) => \App\Services\FdrService::renewWithNetAmount($record)),
                
    

            Action::make('encash')
                ->label('Encash')
                ->color('danger')
                ->icon('heroicon-o-banknotes')
                ->visible(fn ($record) => $record->status === 'active')
                ->requiresConfirmation()
                ->action(fn ($record) => \App\Services\FdrService::encash($record)),

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
            'index' => ManageFdrs::route('/'),
        ];
    }
}
