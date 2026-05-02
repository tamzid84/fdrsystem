<?php

namespace App\Exports\Fdr;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FdrEnterpriseExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data->map(function ($fdr) {
            return [
                'FDR No' => $fdr->fdr_number,
                'Fund' => $fdr->fund->name ?? '',
                'Bank' => $fdr->bank->name ?? '',
                'Amount' => $fdr->amount,
                'Interest' => $fdr->interest_rate,
                'Status' => $fdr->status,
                'Maturity' => $fdr->maturity_date,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'FDR No',
            'Fund',
            'Bank',
            'Amount',
            'Interest',
            'Status',
            'Maturity Date',
        ];
    }
}