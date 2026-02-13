<?php

namespace App\Livewire;
use App\Models\Project;
use App\Models\Clients;
use App\Models\ProjectLedger;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LedgerExport;

use Livewire\Component;

class LedgerProject extends Component
{
    public $filterType = '';      
    public $clientId = null;
    public $projectId = null;
    public $fromDate = null;
    public $toDate = null;

    public $clients = [];
    public $projects = [];

    public function mount()
    {
        $this->clients = Clients::orderBy('company_name')->get();
        
        $this->projects = Project::orderBy('project_name')->get();
       
    }

    public function updatedFilterType()
    {
        $this->reset(['clientId', 'projectId','fromDate', 'toDate']);
    }

    private function getFilteredQuery()
    {
        $query = ProjectLedger::query();

        if ($this->filterType === 'client' && $this->clientId) {
            $projectIds = Project::where('client_id', $this->clientId)->pluck('id');
            $query->whereIn('project_id', $projectIds);
        }

        if ($this->filterType === 'project' && $this->projectId) {
            $query->where('project_id', $this->projectId);
        }

        $query->when($this->fromDate, fn ($q) =>
            $q->whereDate('entry_date', '>=', $this->fromDate)
        )
        ->when($this->toDate, fn ($q) =>
            $q->whereDate('entry_date', '<=', $this->toDate)
        );

        return $query->orderBy('entry_date')->orderBy('id');
    }

    public function getLedgersProperty()
    {
        $ledgers = $this->getFilteredQuery()
            ->with('project')
            ->get();

        $balance = 0;
        $openingBalance = 0;
        if ($this->projectId) {

            $project = Project::find($this->projectId);

            if ($project) {

                $totalAdvance = ProjectLedger::where('project_id', $this->projectId)
                    ->where('type', 'payment')
                    ->sum('debit');

                $openingBalance = $project->project_price - $totalAdvance;

                $balance = $openingBalance;
            }
        }
        foreach ($ledgers as $ledger) {

            $balance += $ledger->debit;

            $balance -= $ledger->credit;

            $ledger->running_balance = $balance;
        }
        $ledgers->opening_balance = $openingBalance;

        return $ledgers;
    }


    // public function getLedgersProperty()
    // {
    //     $ledgers = $this->getFilteredQuery()->get();

    //     $balance = 0;

    //     foreach ($ledgers as $ledger) {
    //         $balance += $ledger->debit;
    //         $balance -= $ledger->credit;
    //         $ledger->running_balance = $balance;
    //     }

    //     return $ledgers;
    // }


    public function exportPdf()
    {
        $ledgers = $this->getFilteredQuery()
            ->with('project')
            ->get();

        $balance = 0;

        foreach ($ledgers as $ledger) {
            $balance += $ledger->debit;
            $balance -= $ledger->credit;
            $ledger->running_balance = $balance;
        }

        $pdf = Pdf::loadView('exports.ledger-pdf', [
            'ledgers' => $ledgers,
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
        ]);

        return response()->streamDownload(
            fn () => print($pdf->output()),
            "ledger.pdf"
        );
    }

    public function exportExcel()
    {
        $data = $this->getFilteredQuery()->get();

        return Excel::download(new LedgerExport($data), 'ledger.xlsx');
    }



    // public function getLedgersProperty()
    // {
    //     $query = ProjectLedger::query();

    //     if ($this->filterType === 'client' && $this->clientId) {
    //         $projectIds = Project::where('client_id', $this->clientId)->pluck('id');
    //         $query->whereIn('project_id', $projectIds);
    //     }

    //     if ($this->filterType === 'project' && $this->projectId) {
    //         $query->where('project_id', $this->projectId);
    //     }

    //     $ledgers = $query
    //         ->when($this->fromDate, fn ($q) =>
    //             $q->whereDate('entry_date', '>=', $this->fromDate)
    //         )
    //         ->when($this->toDate, fn ($q) =>
    //             $q->whereDate('entry_date', '<=', $this->toDate)
    //         )
    //         ->orderBy('entry_date')
    //         ->orderBy('id')
    //         ->get();
    //     $balance = 0;

    //     foreach ($ledgers as $ledger) {
    //         $balance += $ledger->debit;   
    //         $balance -= $ledger->credit; 
    //         $ledger->running_balance = $balance;
    //     }

    //     return $ledgers;
    // }

    public function resetFilters()
    {
        $this->reset([
            'filterType',
            'clientId',
            'projectId',
            'fromDate',
            'toDate',
        ]);

        $this->filterType = '';
    }

    public function render()
    {
        return view('livewire.ledger-project', [
            'ledgers' => $this->ledgers, 
        ])->layout('layouts.app');
    }
}
