<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Clients;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\InvoicePayment;
use Carbon\Carbon;

class Dashboard extends Component
{
    public $totalClients = 0;
    public $projects = 0;
    public $overdueInvoices = 0;
    public $paymentsToday = 0;
    public $pendingInvoices = [];

    public function mount()
    {
        $this->totalClients = Clients::where('status', 1)->count();

        $this->projects = Project::where('status', '1')
            ->count();

        $this->overdueInvoices = Invoice::whereIn('status', [0, 1])
            ->whereDate('created_at', '<', Carbon::today()->subDays(30)) 
            ->count();

        $this->paymentsToday = InvoicePayment::whereDate('created_at', Carbon::today())
            ->sum('paid_amount');

        $this->pendingInvoices = Invoice::whereIn('status', [0, 1])
            ->orderBy('created_at')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard')
            ->layout('layouts.app');
    }
}
