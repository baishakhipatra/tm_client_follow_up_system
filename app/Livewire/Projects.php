<?php

namespace App\Livewire;
use App\Models\Project;
use App\Models\Clients;
use App\Models\ProjectLedger;
use Livewire\Component;
use Livewire\WithPagination;

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

    protected $rules = [
        'project_name' => 'required|string|max:255',
        'client_id' => 'required|exists:clients,id',
        'project_cost' => 'required|numeric|min:0',
        'project_code' => 'nullable|string|max:100',
        'payment_received'  => 'nullable|numeric|min:0',
        'payment_terms' => 'nullable|string|max:100',
        'is_taxable' => 'required|in:0,1',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
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
        //dd($this->validate());
        $this->validate();

        $received = $this->payment_received ?? 0;

        if ($received > $this->project_cost) {
            $received = $this->project_cost;
        }

        $project = Project::updateOrCreate(
            ['id' => $this->projectId],
            [
                'project_name'   => $this->project_name,
                'project_code'   => $this->project_code,
                'client_id'      => $this->client_id,
                'project_cost'   => $this->project_cost,
                'gst_amount'     => $this->gst_amount,
                'total_cost'     => $this->total_cost,
                'is_taxable'     => $this->is_taxable,
                'payment_received'  => $received,
                'payment_terms'  => $this->payment_terms,
                'start_date'     => $this->start_date,
                'end_date'       => $this->end_date,
                'status'         => 1,
            ]
        );

        ProjectLedger::create([
            'project_id' => $project->id,
            'entry_date' => now(),
            'reference'  => $project->project_code,
            'type'       => 'invoice',
            'debit'      => $project->total_cost,
            'credit'     => 0,
            'balance'    => $project->total_cost,
            'description'=> 'Project cost',
        ]);

        if ($received > 0) {
            ProjectLedger::create([
                'project_id' => $project->id,
                'entry_date' => now(),
                'reference'  => 'ADVANCE',
                'type'       => 'payment',
                'debit'      => 0,
                'credit'     => $received,
                'balance'    => $project->total_cost - $received,
                'description'=> 'Advance received',
            ]);
        }


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
        $this->payment_received = $project->payment_received;
        $this->payment_terms = ucwords($project->payment_terms);
        $this->start_date = $project->start_date;
        $this->end_date = $project->end_date;

        $this->isEdit = true;
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


    // public function delete($id)
    // {
    //     Project::findOrFail($id)->delete();
    //      $this->dispatch('toastr', type: 'success', message: 'Project deleted successfully!');
    // }

    public function render()
    {
        $projects = Project::with('client')
            ->withSum('invoices as invoice_total', 'amount')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('project_name', 'like', "%{$this->search}%")
                    ->orWhere('project_code', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.projects', ['projects' => $projects]);
    }

}
