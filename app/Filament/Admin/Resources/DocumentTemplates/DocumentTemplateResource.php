<?php

namespace App\Filament\Admin\Resources\DocumentTemplates;

use App\Models\DocumentTemplate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TagsInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Admin\Resources\DocumentTemplates\Pages\ManageDocumentTemplates;

class DocumentTemplateResource extends Resource
{
    protected static ?string $model = DocumentTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    /* ================= FORM ================= */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('code')
                ->label('Template Code (e.g. fdr_report)')
                ->required()
                ->unique(ignoreRecord: true),

            Textarea::make('content')
                ->label('Template Content (Use {{variables}})')
                ->rows(10)
                ->required(),

            TagsInput::make('variables')
                ->label('Variables (e.g. fdr_number, amount, bank_name)')
                ->required(),
        ]);
    }

    /* ================= TABLE ================= */
    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('code')->badge(),
                TextColumn::make('created_at')->date(),
            ])
            ->filters([])
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

    /* ================= PAGES ================= */
    public static function getPages(): array
    {
        return [
            'index' => ManageDocumentTemplates::route('/'),
        ];
    }
}