<?php

namespace App\Exports;

use App\Models\Fdr;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FdrExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Fdr::with(['fund', 'bank'])->get()->map(function ($fdr) {
            return [
                'FDR No' => $fdr->fdr_number,
                'Account No' => $fdr->fdr_account_number,
                'Bank' => $fdr->bank->name ?? '',
                'Branch' => $fdr->bank->branch_name ?? '',
                'Fund' => $fdr->fund->name ?? '',
                'Amount' => $fdr->amount,
                'Interest Rate' => $fdr->interest_rate,
                'Tenure' => $fdr->tenure,
                'Charge' => $fdr->charge,
                'Status' => $fdr->status,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'FDR No',
            'Account No',
            'Bank',
            'Branch',
            'Fund',
            'Amount',
            'Interest Rate',
            'Tenure',
            'Charge',
            'Status',
        ];
    }
}