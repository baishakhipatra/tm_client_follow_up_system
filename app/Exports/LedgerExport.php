<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LedgerExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $ledgers;
    protected $runningBalance = 0;

    public function __construct(Collection $ledgers)
    {
        $this->ledgers = $ledgers;
    }

    public function collection()
    {
        return $this->ledgers;
    }

    public function headings(): array
    {
        return [
            'Date',
            'Project',
            'Description',
            'Debit',
            'Credit',
            'Running Balance',
        ];
    }

    public function map($ledger): array
    {
        $this->runningBalance += $ledger->debit;
        $this->runningBalance -= $ledger->credit;

        return [
            $ledger->entry_date,
            ucwords(optional($ledger->project)->project_name),
            ucwords($ledger->description),
            number_format($ledger->debit, 2),
            number_format($ledger->credit, 2),
            number_format($this->runningBalance, 2),
        ];
    }
}
