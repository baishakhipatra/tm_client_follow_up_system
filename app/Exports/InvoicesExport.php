<?php

namespace App\Exports;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;


class InvoicesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Invoice::with(['Client', 'project']);

        if ($this->filters['from']) {
            $query->whereDate('invoice_date', '>=', $this->filters['from']);
        }

        if ($this->filters['to']) {
            $query->whereDate('invoice_date', '<=', $this->filters['to']);
        }

        if ($this->filters['client']) {
            $query->where('client_id', $this->filters['client']);
        }

        if ($this->filters['project']) {
            $query->where('project_id', $this->filters['project']);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'Invoice No',
            'Client',
            'Project',
            'Invoice Date',
            'Due Date',
            'Amount',
            'Paid',
            'Pending',
            'Status',
        ];
    }

    public function map($invoice): array
    {
        return [
            $invoice->invoice_number,
            ucwords($invoice->Client->client_name) ?? '',
            ucwords($invoice->project->project_name) ?? '',
            $invoice->invoice_date,
            $invoice->due_date,
            $invoice->amount,
            $invoice->paid_amount,
            $invoice->pending_amount,
            $invoice->status,
        ];
    }
}
