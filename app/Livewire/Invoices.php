<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

class Invoices extends Component
{
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
    public $payment_method;
    public $payment_notes;
    public $selectedInvoice;
    public $paid_amount = 0;
    public $showInvoiceTypeModal = false;
    public $invoice_type = 'tax';



    protected function rules()
    {
        return [
            'invoice_number' => $this->invoice_type === 'tax'
            ? 'required|string|max:255|unique:invoices,invoice_number,' . $this->invoiceId
            : 'required|string|max:255',
            'project_id'     => 'required',
            'invoice_date'   => 'required|date',
            'due_date'       => 'required|date|after_or_equal:invoice_date',
            'amount'         => 'required|numeric|min:0',
            'paid_amount'    => 'nullable|numeric|min:0',
        ];
    }


    protected $messages = [
        'invoice_number.unique' => 'Invoice number must be unique. This invoice number already exists.',
    ];


    public function render()
    {
        return view('livewire.invoices', [
            'invoices'     => Invoice::with('client')->latest()->get(),
            'projectsList' => Project::with('client')->get(),
        ]);
    }


    public function openModal()
    {
        $this->resetValidation();

        $this->invoiceId = null;
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }


    public function saveInvoices()
    {
        $this->validate();

        $project = Project::findOrFail($this->project_id);

        if ($this->isEdit) {
            $invoice = Invoice::findOrFail($this->invoiceId);

            $invoice->update([
                'client_id' => $project->client_id,
                'invoice_number' => $this->invoice_number,
                'invoice_date' => $this->invoice_date,
                'due_date' => $this->due_date,
                'amount' => $this->amount,
                'pending_amount' => $this->pending_amount ?? $this->amount,
            ]);

            $message = 'Invoice updated successfully';
        } else {
            Invoice::create([
                'client_id' => $project->client_id,
                'project_id' => $project->id,
                'invoice_type' => $this->invoice_type,
                'invoice_number' => $this->invoice_number,
                'invoice_date' => $this->invoice_date,
                'due_date' => $this->due_date,
                'amount' => $this->amount,
                'paid_amount'     => 0,
                'pending_amount'  => $project->project_cost - 0,
                'status' => 'unpaid',
            ]);

            $message = 'Invoice created successfully';
        }

        $this->reset();
        $this->showModal = false;
        $this->isEdit = false;

        $this->dispatch('toastr', type: 'success', message: $message);
    }



    public function editInvoice($id)
    {
        $invoice = Invoice::findOrFail($id);

        $this->invoiceId = $invoice->id;
        $this->invoice_number = ucwords($invoice->invoice_number);
        $this->invoice_date = $invoice->invoice_date;
        $this->due_date = $invoice->due_date;
        $this->amount = $invoice->amount;
        $this->pending_amount = $invoice->pending_amount;
        $this->project_id = Project::where('client_id', $invoice->client_id)->first()?->id;

        $this->isEdit = true;
        $this->showModal = true;
    }

    public function openPaymentModal($invoiceId)
    {
        $invoice = Invoice::with('client')->findOrFail($invoiceId);

        $this->selectedInvoice = $invoice;
        $this->payment_invoice_id = $invoice->id;
        $this->payment_amount = $invoice->amount;
        $this->payment_date = now()->format('Y-m-d');
        $this->payment_method = null;
        $this->payment_notes = null;

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

        $invoice = Invoice::findOrFail($this->payment_invoice_id);

        $newPaid = $invoice->paid_amount + $this->payment_amount;

        $pending = max($invoice->amount - $newPaid, 0);

        if ($newPaid <= 0) {
            $status = 'unpaid';
        } elseif ($pending <= 0) {
            $status = 'paid';
            $pending = 0;
        } else {
            $status = 'partially_paid';
        }

        $invoice->update([
            'paid_amount'    => $newPaid,
            'pending_amount' => $pending,
            'status'         => $status,
        ]);

        $this->showPaymentModal = false;

        $this->dispatch('toastr', type: 'success', message: 'Payment recorded successfully');
    }

    public function openInvoiceTypeModal()
    {
        $this->resetValidation();
        $this->invoice_type = 'tax'; // default
        $this->showInvoiceTypeModal = true;
    }

    public function proceedToInvoiceForm()
    {
        $this->showInvoiceTypeModal = false;
        $this->openModal(); // your existing modal
    }

    private function generateNonTaxInvoiceNumber()
    {
        $today = now();

        $fyStart = $today->month >= 4 ? $today->year : $today->year - 1;
        $fyEnd   = $fyStart + 1;

        $financialYear = substr($fyStart, -2) . substr($fyEnd, -2);

        $count = Invoice::where('invoice_type', 'non_tax')
            ->whereYear('created_at', $today->year)
            ->count() + 1;

        $sequence = str_pad($count, 4, '0', STR_PAD_LEFT);

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
            'isEdit'
        ]);
    }
}
