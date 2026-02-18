<?php

namespace App\Livewire;
use App\Models\Project;
use App\Models\Clients;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Payment;
use App\Models\ProjectLedger;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\VoucherNumberService;

class Projects extends Component
{
    use WithPagination;
    public $search = '';
    protected $paginationTheme = 'bootstrap';
    public $showModal = false;
    public $projectId = null;
    public $isEdit = false;

    public $project_name;
    public $project_code;
    public $client_id;
    public $project_cost;
    public $payment_received;
    public $payment_terms;
    public $start_date;
    public $end_date;
    public $clientsList = [];
    public $is_taxable = 0;  
    public $gst_amount = 0;
    public $total_cost = 0;
    public $totalPaymentReceived = 0;

    protected $rules = [
        'project_name' => 'required|string|max:255',
        'client_id' => 'required|exists:clients,id',
        'project_cost' => 'required|numeric|min:0',
        'project_code' => 'nullable|string|max:100',
        'payment_received'  => 'nullable|numeric|min:0|lte:project_cost',
        'payment_terms' => 'nullable|string|max:100',
        'is_taxable' => 'required|in:0,1',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
    ];

    protected $messages = [
        'payment_received.lte' => 'Received amount cannot be greater than project cost.',
    ];

    private function resetForm()
    {
        $this->reset([
            'projectId',
            'project_name',
            'project_code',
            'client_id',
            'project_cost',
            'payment_received',
            'payment_terms',
            'start_date',
            'end_date',
            'is_taxable',
            'gst_amount',
            'total_cost',
        ]);
        $this->is_taxable = 0;
        $this->gst_amount = 0;
        $this->total_cost = 0;
    }

    public function updatedProjectCost()
    {
        $this->calculateGST();
    }

    public function updatedIsTaxable()
    {
        $this->is_taxable = (int) $this->is_taxable;
        $this->calculateGST();
    }

    public function calculateGST()
    {
        $cost = (float) ($this->project_cost ?? 0);

        if ((int) $this->is_taxable === 1) {
            $this->gst_amount = round($cost * 0.18, 2);
        } else {
            $this->gst_amount = 0;
        }

        $this->total_cost = round($cost + $this->gst_amount, 2);
    }

    public function mount(){
        $this->clientsList = Clients::orderBy('company_name')->where('status', 1)->get();
    }

    public function openModal(){
        $this->resetValidation();
        $this->resetForm();
        $this->isEdit = false;
        $this->showModal = true;
    }

    public function closeModal(){
        $this->resetValidation();
        $this->resetForm();
        $this->showModal = false;
    }

    public function saveProject()
    {
        $this->validate();

        DB::transaction(function () {
            $received = $this->payment_received ?? 0;

            $isNewProject = !$this->projectId;

            $project = Project::updateOrCreate(
                ['id' => $this->projectId],
                [
                    'project_name'      => $this->project_name,
                    'project_code'      => $this->project_code,
                    'client_id'         => $this->client_id,
                    'project_cost'      => $this->project_cost,
                    'gst_amount'        => $this->gst_amount,
                    'total_cost'        => $this->total_cost,
                    'is_taxable'        => $this->is_taxable,
                    'payment_received'  => $received,
                    'payment_terms'     => $this->payment_terms,
                    'start_date'        => $this->start_date,
                    'end_date'          => $this->end_date,
                    'status'            => 1,
                ]
            );

            if ($isNewProject) {
                ProjectLedger::create([
                    'project_id' => $project->id,
                    'entry_date' => now()->toDateString(),
                    'reference'  => 'PROJECT-' . ($project->project_code ?? $project->id),
                    'type'       => 'project_created',
                    'debit'      => $project->total_cost,
                    'credit'     => 0,
                    'balance'    => $project->total_cost,
                    'description'=> 'Project created with total cost ₹' . $project->total_cost,
                ]);
            }

            $invoiceNumber = ($this->is_taxable ? 'TAX' : 'NTX') . '-' . now()->format('Ymd') . '-' . str_pad($project->id, 4, '0', STR_PAD_LEFT);

            $status = 0; 

            if ($received > 0 && $received < $project->total_cost) {
                $status = 1; 
            } elseif ($received >= $project->total_cost) {
                $status = 2;
            }

            $invoice = Invoice::create([
                'client_id'                 => $this->client_id,
                'project_id'                => $project->id,
                'invoice_number'            => $invoiceNumber,
                'net_price'                 => $project->total_cost,
                'required_payment_amount'   => $project->total_cost - $received,
                'status'                    => $status,
                'created_by'                => auth()->id(),
            ]);

            if ($received > 0) {

                $voucherNo = VoucherNumberService::generate();

                $payment = Payment::create([
                    'client_id'       => $this->client_id,
                    'payment_amount'  => $received,
                    'payment_method'  => $this->payment_method ?? 'cash',
                    'voucher_no'      => $voucherNo,
                    'status'          => 1,
                ]);

                InvoicePayment::create([
                    'invoice_id'      => $invoice->id,
                    'payment_id'      => $payment->id,
                    'invoice_amount'  => $project->total_cost,
                    'paid_amount'     => $received,
                    'rest_amount'     => $project->total_cost - $received,
                    'invoice_no'      => $invoice->invoice_number,
                ]);
            }
        });

        $this->dispatch('toastr', type: 'success', message:
            $this->isEdit ? 'Project updated successfully!' : 'Project created successfully!'
        );

        $this->closeModal();
    }

    public function editProject($id)
    {
        $this->resetValidation();
        $this->resetForm();

        $project = Project::findOrFail($id);

        $this->projectId = $project->id;
        $this->project_name = ucwords($project->project_name);
        $this->project_code = ucwords($project->project_code);
        $this->client_id = $project->client_id;
        $this->project_cost = $project->project_cost;
        $this->is_taxable = $project->is_taxable;
        $this->gst_amount = $project->gst_amount;
        $this->total_cost = $project->total_cost;
        // $this->payment_received = $project->payment_received;
        $this->payment_terms = ucwords($project->payment_terms);
        $this->start_date = $project->start_date;
        $this->end_date = $project->end_date;

        $this->isEdit = true;

        $this->totalPaymentReceived = InvoicePayment::whereHas('invoice', function ($q) use ($project) {
            $q->where('project_id', $project->id);
        })
        ->sum('paid_amount');
        $this->showModal = true;
    }

    public function confirmDelete($id){
        $this->dispatch('showConfirm', ['itemId' => $id]);
    }

    public function delete($id)
    {
        $project = Project::with('invoices')->findOrFail($id);
        if ($project->invoices()->count() > 0) {

            $this->dispatch('toastr', type: 'error', 
                message: 'Project cannot be deleted. It has related invoices and payments!'
            );
            return;
        }

        $project->delete();

        $this->dispatch('toastr', type: 'success', 
            message: 'Project deleted successfully!'
        );
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetSearch()
    {
        $this->search = '';
        $this->resetPage();
    }

    public function render()
    {
        $projects = Project::with('Client')
            ->withSum(
                ['invoices as received_amount' => function ($q) {
                    $q->join('invoice_payments', 'invoice_payments.invoice_id', '=', 'invoices.id');
                }],
                'invoice_payments.paid_amount'
            )
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('project_name', 'like', "%{$this->search}%")
                    ->orWhere('project_code', 'like', "%{$this->search}%")
                    ->orWhereHas('client', function ($clientQuery) {
                        $clientQuery
                            ->where('client_name', 'like', '%' . $this->search . '%')
                            ->orWhere('company_name', 'like', '%' . $this->search . '%');
                    });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.projects', ['projects' => $projects]);
    }

}
