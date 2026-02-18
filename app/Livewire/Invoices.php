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
    public $payment_id;
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

    public function updatingFilterFromDate() { $this->resetPage(); }
    public function updatingFilterToDate() { $this->resetPage(); }
    public function updatingFilterClient() { $this->resetPage(); }
    public function updatingFilterProject() { $this->resetPage(); }

    // public function render()
    // {
    //     return view('livewire.invoices', [
    //         'invoices'     => Invoice::with('client')->latest()->paginate(10),
    //         'projectsList' => Project::with('client')->get(),
    //     ]);
    // }
    public function render()
    {
        $query = Invoice::with(['client', 'project']);

        if ($this->filter_from_date) {
            $query->whereDate('invoice_date', '>=', $this->filter_from_date);
        }

        if ($this->filter_to_date) {
            $query->whereDate('invoice_date', '<=', $this->filter_to_date);
        }

        if ($this->filter_client) {
            $query->where('client_id', $this->filter_client);
        }

        if ($this->filter_project) {
            $query->where('project_id', $this->filter_project);
        }

        $payments = Payment::with('client')
        ->latest()
        ->paginate(10);

        return view('livewire.invoices', [
            'invoices'     => $query->latest()->paginate(10),
            'projectsList' => Project::with('Client')->get(),
            'clientsList'  => Clients::where('status',1)->get(),
            'payments' => $payments,
        ]);
    }

    public function viewVoucher($paymentId)
    {
        // $this->viewVoucherData = Payment::with([
        //     'client',
        //     'invoicePayments.invoice'
        // ])->findOrFail($paymentId);

        // $this->viewVoucherInvoices = $this->viewVoucherData->invoicePayments;

        // $this->showViewVoucherModal = true;
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
            $this->payment_amount = 0;
            return;
        }

        $this->total_due = Invoice::where('client_id', $clientId)
            ->whereIn('status', [0, 1])
            ->sum('required_payment_amount');

        $this->payment_amount = $this->total_due;
    }

    public function openModal()
    {
        $this->resetValidation();

        $this->invoiceId = null;
        $this->isEdit = false;
        $this->invoice_number = $this->generateVoucherNumber();
        $this->showModal = true;
    }

    public function closeModal()
    {
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



    public function saveVoucher()
    {
        $this->validate([
            'client_id' => 'required',
            'payment_amount' => 'required|numeric|min:1',
        ]);

        Payment::create([
            'client_id'      => $this->client_id,
            'payment_amount' => $this->payment_amount,
            'payment_method' => $this->payment_method,
            'voucher_no'     => $this->invoice_number,
            'status'         => 0, // 0 = unpaid
        ]);

        $this->dispatch('toastr', type: 'success', message: 'Voucher created successfully');
        $this->closeModal();
    }


    // public function storeVoucherPayment()
    // {
    //     DB::transaction(function () {

    //         $payment = Payment::findOrFail($this->payment_id);
    //         $remaining = $payment->payment_amount;

    //         foreach ($this->pay_amounts as $invoiceId => $amount) {

    //             if ($amount <= 0 || $remaining <= 0) continue;

    //             $invoice = Invoice::findOrFail($invoiceId);
    //             $paying = min($amount, $invoice->required_payment_amount, $remaining);

    //             InvoicePayment::create([
    //                 'invoice_id'   => $invoice->id,
    //                 'payment_id'   => $payment->id,
    //                 'invoice_no'   => $invoice->invoice_number,
    //                 'invoice_amount' => $invoice->net_price,
    //                 'paid_amount'  => $paying,
    //                 'rest_amount'  => $invoice->required_payment_amount - $paying,
    //             ]);

    //             $invoice->required_payment_amount -= $paying;
    //             $invoice->status = $invoice->required_payment_amount == 0 ? 2 : 1;
    //             $invoice->save();

    //             $remaining -= $paying;
    //         }

    //         $paidTotal = InvoicePayment::where('payment_id', $payment->id)->sum('paid_amount');
    //         $payment->status = $paidTotal >= $payment->payment_amount ? 2 : 1;
    //         $payment->save();
    //     });

    //     $this->showPaymentModal = false;

    //     $this->dispatch('toastr', type: 'success', message: 'Voucher payment completed');
    // }

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

                if ($remaining <= 0) {
                    break;
                }

                $paying = min($invoice->required_payment_amount, $remaining);

                InvoicePayment::create([
                    'invoice_id'      => $invoice->id,
                    'payment_id'      => $payment->id,
                    'invoice_no'      => $invoice->invoice_number,
                    'invoice_amount'  => $invoice->net_price,
                    'paid_amount'     => $paying,
                    'rest_amount'     => $invoice->required_payment_amount - $paying,
                ]);

                $invoice->required_payment_amount -= $paying;
                $invoice->status = $invoice->required_payment_amount == 0 ? 2 : 1;
                $invoice->save();

                $remaining -= $paying;
            }
            $payment->status = ($remaining == 0) ? 2 : 1;
            $payment->save();
        });

        $this->showPaymentModal = false;

        $this->dispatch(
            'toastr',
            type: 'success',
            message: 'Voucher payment applied successfully'
        );
    }

    // public function saveVoucher()
    // {
    //     $this->validate([
    //         'client_id' => 'required',
    //         'payment_amount' => 'required|numeric|min:1',
    //     ]);

    //     DB::transaction(function () {

    //         $payment = Payment::create([
    //             'client_id'      => $this->client_id,
    //             'payment_amount' => $this->payment_amount,
    //             'payment_method' => $this->payment_method,
    //             'voucher_no'     => $this->invoice_number,
    //         ]);

    //         $amount = floatval($this->payment_amount);

    //         $invoices = Invoice::where('client_id', $this->client_id)
    //             ->where('status', '!=', 2)
    //             ->orderBy('id')
    //             ->get();

    //         foreach ($invoices as $invoice) {
    //             if ($amount <= 0) break;

    //             $paying = min($amount, floatval($invoice->required_payment_amount));
    //             InvoicePayment::create([
    //                 'invoice_id'     => $invoice->id,
    //                 'payment_id'     => $payment->id,
    //                 'invoice_amount' => $invoice->net_price,
    //                 'paid_amount'    => $paying,
    //                 'rest_amount'    => floatval($invoice->required_payment_amount) - $paying,
    //                 'invoice_no'     => $invoice->invoice_number,
    //             ]);

    //             $invoice->required_payment_amount -= $paying;

    //             $invoice->status = $invoice->required_payment_amount == 0 ? 2 : 1;

    //             $invoice->save();
    //             $amount -= $paying;
    //         }
    //     });

    //     $this->dispatch('toastr', type: 'success', message: 'Voucher created successfully');
    //     $this->closeModal();
    // }


    // public function saveInvoices()
    // {
    //     $this->validate();

    //     if ($this->amount > $this->project_due) {
    //         $this->addError('amount', 'Invoice amount cannot exceed project due.');
    //         return;
    //     }

    //     $project = Project::findOrFail($this->project_id);

    //     if ($this->isEdit) {
    //         $invoice = Invoice::findOrFail($this->invoiceId);

    //         $invoice->update([
    //             'client_id' => $project->client_id,
    //             'invoice_number' => $this->invoice_number,
    //             'invoice_date' => $this->invoice_date,
    //             'due_date' => $this->due_date,
    //             'amount' => $this->amount,
    //             'pending_amount' => $this->pending_amount ?? $this->amount,
    //         ]);

    //         $message = 'Invoice updated successfully';
    //     } else {
    //         Invoice::create([
    //             'client_id' => $project->client_id,
    //             'project_id' => $project->id,
    //             'invoice_type' => $this->invoice_type,
    //             'invoice_number' => $this->invoice_number,
    //             'invoice_date' => $this->invoice_date,
    //             'due_date' => $this->due_date,
    //             'amount' => $this->amount,
    //             'paid_amount'     => 0,
    //             'pending_amount'  => $this->amount,
    //             'status' => 'unpaid',
    //         ]);

    //         $lastBalance = ProjectLedger::where('project_id', $project->id) ->latest('id') ->value('balance') ?? 0;

    //         $newBalance = $lastBalance + $this->amount;

    //         ProjectLedger::create([
    //             'project_id' => $project->id,
    //             'entry_date' => $this->invoice_date,
    //             'reference'  => $this->invoice_number,
    //             'type'       => 'invoice',
    //             'debit'      => $this->amount,
    //             'credit'     => 0,
    //             'balance' => $newBalance,
    //             'description'=> 'Invoice raised',
    //         ]);

    //         $message = 'Invoice created successfully';
    //     }

    //     $this->reset();
    //     $this->showModal = false;
    //     $this->isEdit = false;

    //     $this->dispatch('toastr', type: 'success', message: $message);
    // }

    public function editInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);

        $this->invoiceId = $invoice->id;
        $this->invoice_number = ucwords($invoice->invoice_number);
        $this->invoice_date = $invoice->invoice_date;
        $this->due_date = $invoice->due_date;
        $this->amount = $invoice->amount;
        $this->pending_amount = $invoice->pending_amount;
        // $this->project_id = Project::where('client_id', $invoice->client_id)->first()?->id;
        $this->project_id = $invoice->project_id;
        $this->invoice_type = $invoice->invoice_type; 

        $this->isEdit = true;
        $this->showModal = true;
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

    public function storePayment()
    {
        $this->validate([
            'payment_invoice_id' => 'required|exists:invoices,id',
            'payment_amount'     => 'required|numeric|min:1',
            'payment_date'       => 'required|date',
        ]);

        DB::transaction(function () {

            $invoice = Invoice::findOrFail($this->payment_invoice_id);

            if ($this->payment_amount > $invoice->pending_amount) {
                $this->addError('payment_amount', 'Payment exceeds pending amount.');
                return;
            }

            $newPaid = $invoice->paid_amount + $this->payment_amount;
            $pending = max($invoice->amount - $newPaid, 0);

            $status = $pending == 0
                ? 'paid'
                : ($newPaid > 0 ? 'partially_paid' : 'unpaid');

            $invoice->update([
                'paid_amount'    => $newPaid,
                'pending_amount' => $pending,
                'status'         => $status,
                'payment_date'   => $this->payment_date,
                'payment_method' => $this->payment_method,
                'payment_notes'  => $this->payment_notes,
            ]);

            $project = Project::find($invoice->project_id);

            if ($project) {

                $newReceived = ($project->payment_received ?? 0) + $this->payment_amount;
                $newReceived = min($newReceived, $project->total_cost);

                $project->update([
                    'payment_received' => $newReceived,
                ]);

                $lastBalance = ProjectLedger::where('project_id', $project->id)
                    ->latest('id')
                    ->value('balance') ?? 0;

                $newBalance = $lastBalance - $this->payment_amount;

                ProjectLedger::create([
                    'project_id' => $project->id,
                    'entry_date' => $this->payment_date,
                    'reference'  => 'PAY-' . $invoice->invoice_number,
                    'type'       => 'payment',
                    'debit'      => 0,
                    'credit'     => $this->payment_amount,
                    'balance'    => $newBalance,
                    'description'=> 'Payment received against invoice ' . $invoice->invoice_number,
                ]);

            }
        });

        $this->showPaymentModal = false;

        $this->dispatch('toastr', type: 'success', message: 'Payment recorded successfully');
    }

    public function proceedToInvoiceForm()
    {
        $this->showInvoiceTypeModal = false;
        $this->openModal(); 
    }

    private function generateVoucherNumber()
    {
        $today = now();
        $fyStart = $today->month >= 4 ? $today->year : $today->year - 1;
        $fyEnd   = $fyStart + 1;

        $financialYear = substr($fyStart, -2) . substr($fyEnd, -2);
        $lastVoucher = Payment::where('voucher_no', 'like', "TM/{$financialYear}/%")
            ->orderByDesc('id')
            ->value('voucher_no');

        if ($lastVoucher) {
            $lastSequence = (int) substr($lastVoucher, -4);
            $nextSequence = $lastSequence + 1;
        } else {
            $nextSequence = 1;
        }

        $sequence = str_pad($nextSequence, 4, '0', STR_PAD_LEFT);

        return "TM/{$financialYear}/{$sequence}";
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
            'project' => $this->filter_project,
        ];

        return Excel::download(new InvoicesExport($filters), 'invoices.xlsx');
    }

    public function exportPdf()
    {
        $query = Invoice::with(['Client', 'project']);

        if ($this->filter_from_date) {
            $query->whereDate('invoice_date', '>=', $this->filter_from_date);
        }

        if ($this->filter_to_date) {
            $query->whereDate('invoice_date', '<=', $this->filter_to_date);
        }

        if ($this->filter_client) {
            $query->where('client_id', $this->filter_client);
        }

        if ($this->filter_project) {
            $query->where('project_id', $this->filter_project);
        }

        $invoices = $query->latest()->get();

        $pdf = Pdf::loadView('exports.invoices-pdf', compact('invoices'))
            ->setPaper('a4', 'portrait');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "invoices.pdf"
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
