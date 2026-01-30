<?php

namespace App\Livewire;
use Livewire\WithPagination;
use App\Models\Clients as ClientModel;

use Livewire\Component;

class Clients extends Component
{
    use WithPagination;
    public $deleteId;
    public $clientId = null;
    public $isEdit = false;

    public $search = '';
    protected $paginationTheme = 'bootstrap';
    public $showModal = false;

    public $client_name;
    public $company_name;
    public $primary_email;
    public $secondary_email;
    public $phone_number;
    public $gst;
    public $billing_address;

    protected $rules = [
        'client_name' => 'required|string|max:255',
        'company_name' => 'required|string|max:255',
        'primary_email' => 'required|email|max:255',
        'phone_number' => 'required|digits:10',
        'billing_address' => 'required|string|max:255',
    ];

    private function resetForm()
    {
        $this->reset([
            'clientId',
            'client_name',
            'company_name',
            'primary_email',
            'secondary_email',
            'phone_number',
            'gst',
            'billing_address',
        ]);
    }


    public function updatedSearch()
    {
        $this->resetPage();
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

    public function editClient($id)
    {

        $this->resetValidation();   
        $this->resetForm(); 

        $client = ClientModel::findOrFail($id);

        $this->clientId = $client->id;
        $this->client_name = ucwords($client->client_name);
        $this->company_name = ucwords($client->company_name);
        $this->primary_email = $client->primary_email;
        $this->secondary_email = $client->secondary_email;
        $this->phone_number = $client->phone_number;
        $this->gst = $client->gst;
        $this->billing_address = ucwords($client->billing_address);

        $this->isEdit = true;
        $this->showModal = true;
    }


    public function saveClient()
    {
        $this->validate();

        ClientModel::updateOrCreate(
            ['id' => $this->clientId],
            [
                'client_name' => $this->client_name,
                'company_name' => $this->company_name,
                'primary_email' => $this->primary_email,
                'secondary_email' => $this->secondary_email,
                'phone_number' => $this->phone_number,
                'billing_address' => $this->billing_address,
                'gst' => $this->gst,
                'status' => 1,
            ]
        );

        $this->dispatch('toastr', type: 'success', message: 
            $this->isEdit ? 'Client updated successfully!' : 'Client added successfully!'
        );

        $this->closeModal();
    }

    public function toggleStatus($id)
    {
        $client = ClientModel::find($id);

        if ($client) {
            $client->status = !$client->status;
            $client->save();
            $this->dispatch('toastr', type: 'success', message: 'Client status updated successfully');
        }
    }

    public function confirmDelete($id){
        $this->dispatch('showConfirm', ['itemId' => $id]);
    }

    public function delete($id)
    {
        ClientModel::findOrFail($id)->delete();
        $this->dispatch('toastr', type: 'success', message: 'Client deleted successfully!');
    }


    public function render()
    {
        $clients = ClientModel::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('client_name', 'like', "%{$this->search}%")
                    ->orWhere('company_name', 'like', "%{$this->search}%")
                    ->orWhere('primary_email', 'like', "%{$this->search}%")
                    ->orWhere('phone_number', 'like', "%{$this->search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.clients', compact('clients'))
            ->layout('layouts.app');
    }
}
