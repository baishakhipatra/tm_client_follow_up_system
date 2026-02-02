<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Invoice;
use App\Models\Project;

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



    protected $rules = [
        'invoice_number' => 'required|string|max:255',
        'project_id'     => 'required',
        'invoice_date'   => 'required|date',
        'due_date'       => 'required|date|after_or_equal:invoice_date',
        'amount'         => 'required|numeric|min:0',
        'paid_amount' => 'nullable|numeric|min:0',
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
        $this->reset();
        $this->resetValidation();
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
            // CREATE
            Invoice::create([
                'client_id' => $project->client_id,
                'invoice_number' => $this->invoice_number,
                'invoice_date' => $this->invoice_date,
                'due_date' => $this->due_date,
                'amount' => $this->amount,
                'pending_amount' => $this->amount,
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

        $invoice = Invoice::with('client')->findOrFail($this->payment_invoice_id);
        dd($invoice);
        $project = Project::with('client')->findOrFail($invoice->project_id);
        $totalPaid = Invoice::where('project_id', $project->id)
            ->where('status', '!=', 'unpaid')
            ->sum('amount');
        $pending = max($project->total_cost - $totalPaid, 0);

        if ($totalPaid <= 0) {
            $status = 'unpaid';
        } elseif ($pending <= 0) {
            $status = 'paid';
            $pending = 0; 
        } else {
            $status = 'partially_paid';
        }
        $invoice->update([
            'status'         => $status,
            'pending_amount' => $pending,
        ]);

        $this->showPaymentModal = false;

        $this->dispatch('toastr', type: 'success', message: 'Payment recorded successfully');
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
