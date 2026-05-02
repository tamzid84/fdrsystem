<?php

namespace App\Filament\Admin\Resources\Documents;

use App\Models\Document;
use App\Models\DocumentTemplate;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Admin\Resources\Documents\Pages\ManageDocuments;
use App\Services\Document\DocumentExportService;


class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $recordTitleAttribute = 'doc_no';

    /* ================= FORM ================= */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('template_id')
                ->label('Template')
                ->options(DocumentTemplate::pluck('name', 'id'))
                ->searchable()
                ->required(),

           

            Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'issued' => 'Issued',
                ])
                ->default('draft')
                ->required(),

            Textarea::make('data')
                ->label('Document Data (JSON format)')
                ->required()
                ->rows(8)
                ->helperText('Example: {"fdr_number":"123","amount":"10000"}'),
        ]);
    }

    /* ================= TABLE ================= */
    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('doc_no')
            ->columns([
                TextColumn::make('doc_no')->searchable(),

                TextColumn::make('template.name')
                    ->label('Template'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'draft' => 'gray',
                        'pending' => 'warning',
                        'approved' => 'success',
                        'issued' => 'primary',
                    }),

                TextColumn::make('created_at')->date(),
            ])
            ->recordActions([
                EditAction::make(),

                DeleteAction::make(),

                \Filament\Actions\Action::make('word')
                    ->label('Export Word')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->action(function (Document $record) {
                        return app(DocumentExportService::class)
                            ->word($record);
                    }),
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
            'index' => ManageDocuments::route('/'),
        ];
    }
}