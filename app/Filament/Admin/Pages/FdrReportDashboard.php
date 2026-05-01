<?php

namespace App\Filament\Admin\Pages;

use App\Exports\FdrExport;
use App\Models\Fdr;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpWord\PhpWord;
use Filament\Support\Icons\Heroicon;
use BackedEnum;
use UnitEnum;

class FdrReportDashboard extends Page implements HasTable
{
    use InteractsWithTable;

    protected static BackedEnum|string|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?string $navigationLabel = 'FDR Reports';

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected string $view = 'filament.admin.pages.fdr-report-dashboard';

    /**
     * 📊 TABLE (FIXED - NO Table::make)
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(Fdr::with(['fund', 'bank']))
            ->columns([
                TextColumn::make('fdr_number')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fund.name')
                    ->label('Fund'),

                TextColumn::make('bank.name')
                    ->label('Bank'),

                TextColumn::make('amount')
                    ->money('BDT')
                    ->sortable(),

                TextColumn::make('interest_rate')
                    ->label('Interest %'),

                TextColumn::make('status')
                    ->badge(),

                TextColumn::make('maturity_date')
                    ->date(),
            ])
            ->paginated();
    }

    /**
     * 📊 Header Actions
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('excel')
                ->label('Export Excel')
                ->color('success')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn () => $this->exportExcel()),

            Action::make('pdf')
                ->label('Export PDF')
                ->color('danger')
                ->icon('heroicon-o-document-arrow-down')
                ->action(fn () => $this->exportPdf()),

            Action::make('word')
                ->label('Export Word')
                ->color('primary')
                ->icon('heroicon-o-document-text')
                ->action(fn () => $this->exportWord()),
        ];
    }

    /**
     * 📊 Stats
     */
    public function getFdrStats(): array
    {
        return [
            'total_fdr' => Fdr::count(),
            'active_fdr' => Fdr::where('status', 'active')->count(),
            'encashed' => Fdr::where('status', 'encashed')->count(),
            'total_investment' => Fdr::sum('amount'),
        ];
    }

    /**
     * 📊 Excel Export
     */
    public function exportExcel()
    {
        return Excel::download(new FdrExport, 'fdr-report.xlsx');
    }

    /**
     * 📄 PDF Export
     */
    public function exportPdf()
    {
        $fdrs = Fdr::with(['fund', 'bank'])->get();

        $pdf = Pdf::loadView('reports.fdr_pdf', compact('fdrs'));

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'fdr-report.pdf'
        );
    }

    /**
     * 📝 Word Export
     */
    public function exportWord()
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $fdrs = Fdr::with(['fund', 'bank'])->get();

        foreach ($fdrs as $fdr) {
            $section->addText(
                "FDR: {$fdr->fdr_number} | Amount: {$fdr->amount} | Status: {$fdr->status}"
            );
        }

        $file = storage_path('fdr-report.docx');
        $phpWord->save($file, 'Word2007');

        return response()->download($file)->deleteFileAfterSend(true);
    }
}