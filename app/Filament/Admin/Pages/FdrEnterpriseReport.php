<?php

namespace App\Filament\Admin\Pages;

use App\Models\Fdr;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;
use App\Exports\Fdr\FdrEnterpriseExport;
use PhpOffice\PhpWord\PhpWord;

class FdrEnterpriseReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $navigationLabel = 'Enterprise Reports';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected string $view = 'filament.admin.pages.fdr-enterprise-report';

    /* ================= BASE QUERY ================= */

    protected function baseQuery(): Builder
    {
        return Fdr::query()->with(['fund', 'bank']);
    }

    /* ================= FILTER QUERY ================= */

    protected function getQuery(): Builder
    {
        $query = $this->baseQuery();
        $filters = $this->tableFilters ?? [];

        return $query
            ->when($filters['fund_id']['value'] ?? null,
                fn ($q, $v) => $q->where('fund_id', $v))

            ->when($filters['bank_id']['value'] ?? null,
                fn ($q, $v) => $q->where('bank_id', $v))

            ->when($filters['status']['value'] ?? null,
                fn ($q, $v) => $q->where('status', $v))

            ->when($filters['fdr_number'] ?? null, function ($q, $filter) {

    $values = $filter['values'] ?? $filter['value'] ?? $filter;

    if (!empty($values)) {
        $q->whereIn('fdr_number', (array) $values);
    }

})

            ->when($filters['date_range']['from'] ?? null,
                fn ($q, $v) => $q->whereDate('start_date', '>=', $v))

            ->when($filters['date_range']['to'] ?? null,
                fn ($q, $v) => $q->whereDate('start_date', '<=', $v))

            ->when($filters['amount_range']['min'] ?? null,
                fn ($q, $v) => $q->where('amount', '>=', $v))

            ->when($filters['amount_range']['max'] ?? null,
                fn ($q, $v) => $q->where('amount', '<=', $v));
    }

    /* ================= TABLE ================= */

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(fn () => $this->getQuery())
            ->columns([
                Tables\Columns\TextColumn::make('fdr_number')->searchable(),
                Tables\Columns\TextColumn::make('fund.name'),
                Tables\Columns\TextColumn::make('bank.name'),
                Tables\Columns\TextColumn::make('amount')->money('BDT'),
                Tables\Columns\TextColumn::make('status')->badge(),
                Tables\Columns\TextColumn::make('maturity_date')->date(),
            ])
            ->filters($this->getFilters())
            ->paginated([10, 25, 50]);
    }

    /* ================= FILTERS ================= */

    protected function getFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('fund_id')
                ->relationship('fund', 'name'),

            Tables\Filters\SelectFilter::make('bank_id')
                ->relationship('bank', 'name'),

            Tables\Filters\Filter::make('fdr_number')
                ->form([
                    Forms\Components\Select::make('values')
                        ->multiple()
                        ->searchable()
                        ->options(Fdr::pluck('fdr_number', 'fdr_number'))
                ])
                ->query(fn ($query, $data) =>
                    $query->when($data['values'] ?? null,
                        fn ($q) => $q->whereIn('fdr_number', $data['values'])
                    )
                ),

            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'active' => 'Active',
                    'renewed' => 'Renewed',
                    'encashed' => 'Encashed',
                ]),
        ];
    }

    /* ================= HEADER ACTIONS ================= */

    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Export Report')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    Forms\Components\Select::make('type')
                        ->options([
                            'excel' => 'Excel',
                            'pdf' => 'PDF',
                            'word' => 'Word (All)',
                            'word_bank' => 'Word (Bank Wise)',
                        ])
                        ->required(),
                ])
                ->action(fn ($data) => $this->export($data['type'])),
        ];
    }

    /* ================= EXPORT ROUTER ================= */

    public function export($type)
    {
        $data = $this->getFilteredTableQuery()->get();

        return match ($type) {
            'excel' => $this->exportExcel($data),
            'pdf' => $this->exportPdf($data),
            'word' => $this->exportWord($data),
            'word_bank' => $this->exportWordBankWise($data),
        };
    }

    /* ================= WORD EXPORT (ALL) ================= */

 
     public function exportWord($data)
{
    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();

    /* ================= HEADER ================= */

    $section->addText('সিলেট পল্লী বিদ্যুৎ সমিতি-২', ['bold' => true, 'size' => 14]);
    $section->addText('দরবস্ত, জৈন্তাপুর, সিলেট।');

    $section->addTextBreak(1);

    $section->addText('তারিখঃ ৩০/০৪/২০২৬ ইং');
    $section->addText('বিষয়ঃ স্থায়ী আমানত মুনাফাসহ আসল নবায়ন করন প্রসঙ্গে।');

    $section->addTextBreak(2);

    $section->addText(
        'উপর্যুক্ত বিষয়ের প্রেক্ষিতে জানানো যাচ্ছে যে, অত্র সমিতির নিন্মোক্ত স্থায়ী আমানত সমূহ পাশ্বে বর্ণিত তারিখ অনুযায়ী মেয়াদ উত্তীর্ণ হবে। মেয়াদ উত্তীর্ণ স্থায়ী আমানতের তালিকা নিন্মরুপ:'
    );

    $section->addTextBreak(2);

    /* ================= FDR TABLE ================= */

    $table = $section->addTable([
        'borderSize' => 6,
        'borderColor' => '999999',
    ]);

    $table->addRow();

    $table->addCell(2000)->addText('FDR No', ['bold' => true]);
    $table->addCell(2000)->addText('Fund', ['bold' => true]);
    $table->addCell(2000)->addText('Bank', ['bold' => true]);
    $table->addCell(2000)->addText('Amount', ['bold' => true]);
    $table->addCell(2000)->addText('Status', ['bold' => true]);

    foreach ($data as $fdr) {
        $table->addRow();

        $table->addCell()->addText($fdr->fdr_number);
        $table->addCell()->addText($fdr->fund->name ?? '');
        $table->addCell()->addText($fdr->bank->name ?? '');
        $table->addCell()->addText($fdr->amount);
        $table->addCell()->addText($fdr->status);
    }
$section->addTextBreak(2);

        $section->addText('সদয় অবগতি ও প্রয়োজনীয় ব্যবস্থা গ্রহণের জন্য অনুরোধ করা হলো।');

        $section->addTextBreak(2);
   

    

    /* =========================================================
       ✅ FIXED SIGNATURE (PROPER TABLE STYLE - REAL SOLUTION)
       ========================================================= */

    // 🔥 Create REAL table style (this is the key fix)
    $signatureStyle = [
        'borderSize' => 0,
        'borderColor' => 'FFFFFF',
        'cellMargin' => 80,
        'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
    ];

    $phpWord->addTableStyle('SignatureStyle', $signatureStyle);

    $signatureTable = $section->addTable('SignatureStyle');

    /* ROW 1 */
    $signatureTable->addRow();
        $signatureTable->addCell(3500)->addText('(মোহাম্মদ হাবিবুল্লাহ ভূঞা)');
        $signatureTable->addCell(3500)->addText('(কামরুন নাহার)');
        $signatureTable->addCell(3500)->addText('(তুহিন রহমান)');

        $signatureTable->addRow();
        $signatureTable->addCell()->addText('সহকারী হিসাব রক্ষক');
        $signatureTable->addCell()->addText('হিসাব রক্ষক');
        $signatureTable->addCell()->addText('এজিএম (অর্থ-হিসাব)');

        $signatureTable->addRow();
        $signatureTable->addCell()->addText('সিলেট পল্লী বিদ্যুৎ সমিতি-২');
        $signatureTable->addCell()->addText('সিলেট পল্লী বিদ্যুৎ সমিতি-২');
        $signatureTable->addCell()->addText('সিলেট পল্লী বিদ্যুৎ সমিতি-২');

    /* ROW 3 */
    

    /* ================= SAVE ================= */

    $file = storage_path('fdr-report.docx');

    $phpWord->save($file, 'Word2007');

    return response()->download($file)->deleteFileAfterSend(true);
}




    

    /* ================= WORD EXPORT (BANK WISE) ================= */

   public function exportWordBankWise($data)
{
    $phpWord = new \PhpOffice\PhpWord\PhpWord();

    // ✅ GROUP BY BANK
    $grouped = $data->groupBy('bank_id');

    foreach ($grouped as $bankId => $fdrs) {

        $section = $phpWord->addSection();

        $bankName = optional($fdrs->first()->bank)->name;

        /* ================= HEADER ================= */

        $section->addText('সিলেট পল্লী বিদ্যুৎ সমিতি-২', ['bold' => true, 'size' => 14]);
        $section->addText('দরবস্ত, জৈন্তাপুর, সিলেট।');

        $section->addTextBreak(1);

        $section->addText('তারিখঃ ' . now()->format('d/m/Y') . ' ইং');

        $section->addText("ব্যবস্থাপক");
        $section->addText($bankName ?? '');

        $section->addTextBreak(1);

        $section->addText('বিষয়ঃ স্থায়ী আমানত নবায়ন করন প্রসঙ্গে।');

        $section->addTextBreak(2);

        $section->addText(
            'উপর্যুক্ত বিষয়ের প্রেক্ষিতে জানানো যাচ্ছে যে, অত্র সমিতির নিন্মোক্ত স্থায়ী আমানত সমূহ পাশ্বে বর্ণিত তারিখ অনুযায়ী মেয়াদ উত্তীর্ণ হবে। মেয়াদ উত্তীর্ণের তারিখ হতে পরবর্তী ০১ (এক) বছরের জন্য মুনাফা সহ মূলধন নবায়ন করা প্রয়োজন।'
        );

        $section->addTextBreak(2);

        /* ================= TABLE ================= */

        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '999999',
        ]);

        $table->addRow();
        $table->addCell(2000)->addText('FDR No', ['bold' => true]);
        $table->addCell(2000)->addText('Fund', ['bold' => true]);
        $table->addCell(2000)->addText('Amount', ['bold' => true]);
        $table->addCell(2000)->addText('Status', ['bold' => true]);
        $table->addCell(2000)->addText('Maturity', ['bold' => true]);

        foreach ($fdrs as $fdr) {
            $table->addRow();

            $table->addCell()->addText($fdr->fdr_number);
            $table->addCell()->addText($fdr->fund->name ?? '');
            $table->addCell()->addText($fdr->amount);
            $table->addCell()->addText($fdr->status);
            $table->addCell()->addText(optional($fdr->maturity_date)->format('d-m-Y'));
        }

         $section->addTextBreak(2);

    $section->addText('বর্ণিত আমানত এর স্থায়ী আমানত মেয়াদ উত্তীর্ণের তারিখ হতে পরবর্তী ০১(এক) বৎসরের জন্য মুনাফা সহ আসল নবায়ন করতঃ, স্থায়ী আমানতের এর ব্যাংক হিসাব বিবরনী প্রেরণ করার জন্য অনুরোধ করা হল।
নবায়নকৃত ¯হায়ী আমানত অত্র সমিতির জেনারেল ম্যানেজার ও সহকারী জেনারেল ম্যানেজার (অর্থ-হিসাব) এর যৌথ স্বাক্ষরে পরিচালিত হবে। এতদ্সংগে ¯হায়ী আমানতের রশিদ সংযুক্ত করা হল।

বিঃ দ্রঃ-
স্থায়ী আমানতের রশিদটিতে নবায়নকৃত টাকার পরিমাণ, মুনাফার হার, নবায়নের তারিখ, উল্লেখ পূর্বক দুই জনের স্বাক্ষর ও শীল মোহর প্রদানের জন্য অনুরোধ করা হল।


সংযুক্তিঃ বর্ণিত স্থায়ী আমানতের রশিদ।');

    $section->addTextBreak(2);


        /* ================= SIGNATURE ================= */

        $phpWord->addTableStyle('SignatureStyle', [
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
        ]);

        $signatureTable = $section->addTable('SignatureStyle');

        $signatureTable->addRow();


    $signatureTable->addCell(3500)->addText('(তুহিন রহমান)');
    $signatureTable->addCell(3500)->addText('(মোঃ রবিউল হক)');

    /* ROW 2 */
    $signatureTable->addRow();

    $signatureTable->addCell()->addText('এজিএম (অর্থ-হিসাব)');
    $signatureTable->addCell()->addText('জেনারেল ম্যানেজার');

    $section->addTextBreak(2);

    $section->addText(
        'অনুলিপিঃ
        ০১। অফিস/মাষ্টার কপি।'
    );

    $section->addTextBreak(2);
        
    }

    $file = storage_path('fdr-bank-wise-report.docx');

    $phpWord->save($file, 'Word2007');

    return response()->download($file)->deleteFileAfterSend(true);
}

    /* ================= PDF ================= */

    public function exportPdf($data)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reports.fdr.pdf',
            compact('data')
        );

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'fdr.pdf'
        );
    }

    /* ================= EXCEL ================= */

    public function exportExcel($data)
    {
        return \Maatwebsite\Excel\Facades\Excel::download(
            new FdrEnterpriseExport($data),
            'fdr.xlsx'
        );
    }
}