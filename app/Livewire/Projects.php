<?php

namespace App\Livewire;
use App\Models\Project;
use App\Models\Clients;
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

    protected $rules = [
        'project_name' => 'required|string|max:255',
        'client_id' => 'required|exists:clients,id',
        'project_cost' => 'required|numeric|min:0',
        'project_code' => 'nullable|string|max:100',
        'payment_received'  => 'nullable|numeric|min:0',
        'payment_terms' => 'nullable|string|max:100',
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
        ]);
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

        Project::updateOrCreate(
            ['id' => $this->projectId],
            [
                'project_name'   => $this->project_name,
                'project_code'   => $this->project_code,
                'client_id'      => $this->client_id,
                'project_cost'   => $this->project_cost,
                'payment_received'  => $this->payment_received ?? 0,
                'payment_terms'  => $this->payment_terms,
                'start_date'     => $this->start_date,
                'end_date'       => $this->end_date,
                'status'         => 1,
            ]
        );

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
        Project::findOrFail($id)->delete();
         $this->dispatch('toastr', type: 'success', message: 'Project deleted successfully!');
    }


    public function render()
    {
        $projects = Project::with('client')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('project_name', 'like', "%{$this->search}%")
                    ->orWhere('project_code', 'like', "%{$this->search}%");
                });
            })->orderBy('created_at', 'desc')->paginate(10);
        return view('livewire.projects' , ['projects' => $projects]);
    }
}
