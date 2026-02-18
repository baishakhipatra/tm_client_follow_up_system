<?php

namespace App\Exports;

use App\Models\Payment;
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
        $query = Payment::with('Client');

        if ($this->filters['from']) {
            $query->whereDate('created_at', '>=', $this->filters['from']);
        }

        if ($this->filters['to']) {
            $query->whereDate('created_at', '<=', $this->filters['to']);
        }

        if ($this->filters['client']) {
            $query->where('client_id', $this->filters['client']);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'Voucher No',
            'Client',
            'Amount',
            'Payment Method',
            'Status',
            'Date',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->voucher_no,
            $payment->Client->company_name ?? '',
            $payment->payment_amount,
            strtoupper($payment->payment_method),
            $payment->status == 1 ? 'Paid' : 'Pending',
            $payment->created_at->format('d-m-Y'),
        ];
    }
    // protected $filters;

    // public function __construct($filters)
    // {
    //     $this->filters = $filters;
    // }

    // public function query()
    // {
    //     $query = Invoice::with(['Client', 'project']);

    //     if ($this->filters['from']) {
    //         $query->whereDate('invoice_date', '>=', $this->filters['from']);
    //     }

    //     if ($this->filters['to']) {
    //         $query->whereDate('invoice_date', '<=', $this->filters['to']);
    //     }

    //     if ($this->filters['client']) {
    //         $query->where('client_id', $this->filters['client']);
    //     }

    //     if ($this->filters['project']) {
    //         $query->where('project_id', $this->filters['project']);
    //     }

    //     return $query->latest();
    // }

    // public function headings(): array
    // {
    //     return [
    //         'Invoice No',
    //         'Client',
    //         'Project',
    //         'Invoice Date',
    //         'Due Date',
    //         'Amount',
    //         'Paid',
    //         'Pending',
    //         'Status',
    //     ];
    // }

    // public function map($invoice): array
    // {
    //     return [
    //         $invoice->invoice_number,
    //         ucwords($invoice->Client->client_name) ?? '',
    //         ucwords($invoice->project->project_name) ?? '',
    //         $invoice->invoice_date,
    //         $invoice->due_date,
    //         $invoice->amount,
    //         $invoice->paid_amount,
    //         $invoice->pending_amount,
    //         $invoice->status,
    //     ];
    // }
}
