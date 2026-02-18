<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\InvoicePayment;
use App\Models\Project;
use App\Models\Clients;
use App\Models\ProjectLedger;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoicesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\InvoiceReminderService;
use App\Services\VoucherNumberService;

class Invoices extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $showModal = false;
    public $invoiceId = null;
    public $isEdit = false;

    public $invoice_number;
    public $amount;
    public $pending_amount;
    public $project_id;
    public $invoice_date;
    public $due_date;

    public $showPaymentModal = false;
    public $payment_invoice_id;
    public $payment_amount;
    public $payment_date;
    public $payment_method = 'cash';
    public $payment_notes;
    public $selectedInvoice;
    public $paid_amount = 0;
    public $showInvoiceTypeModal = false;
    public $invoice_type = 'tax';
    public $project_due = 0;
    public $isViewMode = false;
    public $filter_from_date;
    public $filter_to_date;
    public $filter_client;
    public $filter_project;
    public $client_id;
    public $total_due = 0;
    public $selectedPayment;
    public $selectedInvoices = [];
    public $payment_id = null;
    public $clientInvoices = [];
    public $showViewVoucherModal = false;
    public $viewVoucherData;
    public $viewVoucherInvoices = [];
    public $voucherAmount = 0;
    

    protected function rules()
    {
        return [
            'invoice_number' => 'required|string|unique:invoices,invoice_number,' . $this->invoiceId,
            'project_id'     => 'required',
            'invoice_date'   => 'required|date',
            'due_date'       => 'required|date|after_or_equal:invoice_date',
            'amount'         => 'required|numeric|min:0',
        ];
    }

    protected $messages = [
        'invoice_number.unique' => 'Voucher number must be unique. This voucher number already exists.',
    ];

    public function updatedFilterFromDate() { $this->resetPage(); }
    public function updatedFilterToDate() { $this->resetPage(); }
    public function updatedFilterClient() { $this->resetPage(); }
    public function updatedFilterProject() { $this->resetPage(); }

    public function render()
    {
        $query = Invoice::with(['client', 'project']);

        if ($this->filter_client) {
            $query->where('client_id', $this->filter_client);
        }

        if ($this->filter_project) {
            $query->where('project_id', $this->filter_project);
        }

        $paymentsQuery = Payment::with('client');

        if ($this->filter_from_date) {
            $paymentsQuery->whereDate('created_at', '>=', $this->filter_from_date);
        }

        if ($this->filter_to_date) {
            $paymentsQuery->whereDate('created_at', '<=', $this->filter_to_date);
        }

        if ($this->filter_client) {
            $paymentsQuery->where('client_id', $this->filter_client);
        }

        $payments = $paymentsQuery->latest()->paginate(10);

        return view('livewire.invoices', [
            'invoices'     => $query->latest()->paginate(10),
            'projectsList' => Project::with('Client')->get(),
            'clientsList'  => Clients::where('status',1)->get(),
            'payments' => $payments,
        ]);
    }

    public function viewVoucher($paymentId)
    {
        $payment = Payment::with('Client')->findOrFail($paymentId);

        $this->payment_id    = $payment->id;
        $this->voucherAmount = $payment->payment_amount;

        $this->clientInvoices = Invoice::where('client_id', $payment->client_id)
            ->whereIn('status', [0, 1])
            ->orderBy('created_at')
            ->get();

        $this->showPaymentModal = true;
    }

    public function viewPaymentDetails($invoiceId)
    {
        $invoice = Invoice::with('Client')->findOrFail($invoiceId);

        $this->selectedInvoice = $invoice;
        $this->payment_invoice_id = $invoice->id;
        $this->payment_amount = $invoice->paid_amount;
        $this->payment_date = $invoice->payment_date;
        $this->payment_method = $invoice->payment_method;
        $this->payment_notes = $invoice->payment_notes;

        $this->isViewMode = true; 
        $this->showPaymentModal = true;
    }

    public function ChangeclientId($clientId)
    {
        if (!$clientId) {
            $this->total_due = 0;

            if (!$this->isEdit) {
                $this->payment_amount = 0;
            }
            return;
        }

        $this->total_due = Invoice::where('client_id', $clientId)
            ->whereIn('status', [0, 1])
            ->sum('required_payment_amount');

        if (!$this->isEdit) {
            $this->payment_amount = $this->total_due;
        }
    }

    public function openModal()
    {
        $this->resetValidation();

        $this->invoiceId = null;
        $this->isEdit = false;
        $this->invoice_number = VoucherNumberService::generate();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->reset([
            'payment_id',
            'client_id',
            'payment_amount',
            'payment_method',
            'isEdit',
        ]);
        $this->showModal = false;
        $this->resetForm();
    }

    public function openPayModal($paymentId)
    {
        $this->payment_id = $paymentId;

        $payment = Payment::findOrFail($paymentId);
        $this->voucherAmount = $payment->payment_amount; 

        $this->clientInvoices = Invoice::where('client_id', $payment->client_id)
            ->whereIn('status', [0,1])
            ->get();

        $this->showPaymentModal = true;
    }

    public function editVoucher($id)
    {
        $payment = Payment::findOrFail($id);

        if ($payment->status == 1) {
            $this->dispatch('toastr', type: 'error', message: 'Paid vouchers cannot be edited');
            return;
        }

        $this->payment_id     = $payment->id;
        $this->invoice_number = $payment->voucher_no;
        $this->client_id      = $payment->client_id;
        $this->payment_amount = $payment->payment_amount;
        $this->payment_method = $payment->payment_method;

        $this->isEdit   = true;

        $this->ChangeclientId($payment->client_id);
        $this->showModal = true;
    }

    private function resetVoucherForm()
    {
        $this->payment_id = null;
        $this->client_id = null;
        $this->payment_amount = null;
        $this->payment_method = null;
        $this->invoice_number = null;
        $this->total_due = 0;
        $this->isEdit = false;
    }

    public function saveVoucher()
    {
        $this->validate([
            'client_id' => 'required',
            'payment_amount' => 'required|numeric|min:1',
        ]);

        if ($this->isEdit && $this->payment_id) {

            $payment = Payment::lockForUpdate()->findOrFail($this->payment_id);

            if ($payment->status == 1) {
                $this->dispatch('toastr', type: 'error', message: 'Paid voucher cannot be edited');
                return;
            }

            $payment->update([
                'client_id'      => $this->client_id,
                'payment_amount' => $this->payment_amount,
                'payment_method' => $this->payment_method,
            ]);

            $message = 'Voucher updated successfully';

        } 
        else {

            Payment::create([
                'client_id'      => $this->client_id,
                'payment_amount' => $this->payment_amount,
                'payment_method' => $this->payment_method,
                'voucher_no'     => $this->invoice_number,
                'status'         => 0, 
            ]);

            $message = 'Voucher created successfully';
        }

        $this->dispatch('toastr', type: 'success', message: $message);

        $this->resetVoucherForm();
        $this->closeModal();
    }

    public function storeVoucherPayment()
    {
        DB::transaction(function () {

            $payment = Payment::findOrFail($this->payment_id);
            $remaining = $payment->payment_amount;

            $invoices = Invoice::where('client_id', $payment->client_id)
                ->whereIn('status', [0, 1])
                ->orderBy('created_at')
                ->get();

            foreach ($invoices as $invoice) {

                if ($remaining <= 0) break;
                if (!$invoice->project_id) {
                    throw new \Exception("Invoice {$invoice->id} has no project_id");
                }

                $invoiceDue = $invoice->required_payment_amount;
                $paying = min($invoiceDue, $remaining);

                InvoicePayment::create([
                    'invoice_id'     => $invoice->id,
                    'payment_id'     => $payment->id,
                    'invoice_no'     => $invoice->invoice_number,
                    'invoice_amount' => $invoice->net_price,
                    'paid_amount'    => $paying,
                    'rest_amount'    => $invoiceDue - $paying,
                ]);

                $invoice->update([
                    'required_payment_amount' => $invoiceDue - $paying,
                    'status' => ($invoiceDue - $paying) == 0 ? 2 : 1,
                ]);
                $lastBalance = ProjectLedger::where('project_id', $invoice->project_id)
                    ->latest('id')
                    ->value('balance') ?? 0;

                ProjectLedger::create([
                    'project_id'  => $invoice->project_id,
                    'entry_date'  => now()->toDateString(),
                    'reference'   => 'VOUCHER-' . $payment->voucher_no,
                    'type'        => 'voucher_payment',
                    'debit'       => 0,
                    'credit'      => $paying,
                    'balance'     => $lastBalance - $paying,
                    'description' => 'Voucher payment applied',
                ]);

                $remaining -= $paying;
            }
            $payment->update([
                'status' => 1
            ]);
        });

        $this->showPaymentModal = false;

        $this->dispatch('toastr', type: 'success', message: 'Voucher payment applied successfully');
    }

    public function openPaymentModal($invoiceId)
    {
        $invoice = Invoice::with('Client')->findOrFail($invoiceId);

        $this->selectedInvoice = $invoice;
        $this->payment_invoice_id = $invoice->id;
        $this->payment_amount = $invoice->amount;
        $this->payment_date = now()->format('Y-m-d');
        $this->payment_method = null;
        $this->payment_notes = null;

        $this->isViewMode = false;
        $this->resetValidation();
        $this->showPaymentModal = true;
    }

    public function proceedToInvoiceForm()
    {
        $this->showInvoiceTypeModal = false;
        $this->openModal(); 
    }

    public function updatedInvoiceType($value)
    {
        if ($value === 'non_tax') {
            $this->invoice_number = $this->generateNonTaxInvoiceNumber();
        } else {
            $this->invoice_number = null;
        }
    }


    private function resetForm()
    {
        $this->reset([
            'invoiceId',
            'invoice_number',
            'amount',
            'project_id',
            'invoice_date',
            'due_date',
            'isEdit',
        ]);
    }

    public function resetFilters()
    {
        $this->reset([
            'filter_from_date',
            'filter_to_date',
            'filter_client',
            'filter_project',
        ]);

        $this->resetPage(); 
    }

    public function exportExcel()
    {
        $filters = [
            'from'    => $this->filter_from_date,
            'to'      => $this->filter_to_date,
            'client'  => $this->filter_client,
        ];

        return Excel::download(new InvoicesExport($filters), 'voucher.xlsx');
    }

    public function exportPdf()
    {
        $query = Payment::with('Client');

        if ($this->filter_from_date) {
            $query->whereDate('created_at', '>=', $this->filter_from_date);
        }

        if ($this->filter_to_date) {
            $query->whereDate('created_at', '<=', $this->filter_to_date);
        }

        if ($this->filter_client) {
            $query->where('client_id', $this->filter_client);
        }

        $vouchers = $query->latest()->get();

        $pdf = Pdf::loadView('exports.vouchers-pdf', compact('vouchers'))
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'vouchers.pdf'
        );
    }

    public function sendDueReminder($invoiceId, InvoiceReminderService $service)
    {
        $invoice = Invoice::with(['Client', 'project'])->findOrFail($invoiceId);

        if ($service->sendReminder($invoice)) {
            $this->dispatch('toastr',
                type: 'success',
                message: 'Invoice reminder email sent successfully.');
        } else {
            $this->dispatch('toastr',
                type: 'error',
                message: 'Unable to send reminder. Check invoice status or email.');
        }
    }

}
