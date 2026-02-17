<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Clients;
use App\Models\Invoice;
use App\Models\Project;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $totalClients;
    public $projects;
    public $overdueInvoices;
    public $paymentsToday;
    public $pendingInvoices;

    public function mount()
    {
        $this->totalClients = Clients::where('status', 1)->count();

        $this->projects = Project::where('status', '1')
            ->count();

        // $this->overdueInvoices = Invoice::whereIn('status', ['unpaid', 'partially_paid'])
        //     ->whereDate('due_date', '<', Carbon::today())
        //     ->count();

        // $this->paymentsToday = Invoice::where('status', 'paid')
        //     ->whereDate('updated_at', Carbon::today())
        //     ->sum('amount');

        // $this->pendingInvoices = Invoice::where('status', 'unpaid')
        //     ->orderBy('due_date')
        //     ->limit(5)
        //     ->get();
    }
    public function render()
    {
        return view('livewire.dashboard')
            ->layout('layouts.app');
    }
}
