<div class="container-fluid py-4">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">

            <div class="fw-semibold d-flex align-items-center">
                <i class="bi bi-funnel me-2"></i> 
                Project Ledger Filter
            </div>

            <div class="d-flex align-items-center gap-2">
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-1"
                            type="button"
                            data-bs-toggle="dropdown">
                        <i class="bi bi-download"></i>
                        Export
                    </button>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li>
                            <a href="#"
                            class="dropdown-item d-flex align-items-center gap-2"
                            wire:click.prevent="exportExcel">
                                <i class="bi bi-file-earmark-excel text-success"></i>
                                Export to Excel
                            </a>
                        </li>
                        <li>
                            <a href="#"
                            class="dropdown-item d-flex align-items-center gap-2"
                            wire:click.prevent="exportPdf">
                                <i class="bi bi-file-earmark-pdf text-danger"></i>
                                Export to PDF
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="row g-3 align-items-end">

               
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Filter By</label>
                    <select class="form-select" wire:model.live="filterType">
                        <option value="">Select Filter</option>
                        <option value="client">Client</option>
                        <option value="project">Project</option>
                    </select>
                </div>

             
                @if($filterType === 'client')
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Client</label>
                        <select class="form-select" wire:model.live="clientId">
                            <option value="">Select Client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}">
                                    {{ ucwords($client->company_name) }} 
                                    ({{ ucwords($client->client_name) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

               
                @if($filterType === 'project')
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Project</label>
                        <select class="form-select" wire:model.live="projectId">
                            <option value="">Select Project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}">
                                    {{ ucwords($project->project_name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                
                <div class="col-md-2">
                    <label class="form-label fw-semibold">From</label>
                    <input type="date"
                        class="form-control"
                        wire:model.live="fromDate">
                </div>

                
                <div class="col-md-2">
                    <label class="form-label fw-semibold">To</label>
                    <input type="date"
                        class="form-control"
                        wire:model.live="toDate">
                </div>

                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button"
                            class="btn btn-outline-secondary w-100"
                            wire:click="resetFilters">
                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                        Reset
                    </button>
                </div>

            </div>
        </div>

    </div>

    @if($ledgers->count())
        <div class="card shadow-sm border-0 mt-4">
            <div class="card-header bg-white d-flex align-items-center justify-content-between">
                <div class="fw-semibold">
                    <i class="bi bi-journal-text me-2 text-primary"></i>
                    Project Ledger
                </div>
                <small class="text-muted">
                    Total Entries: {{ $ledgers->count() }}
                </small>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr class="text-uppercase small text-muted">
                                <th>Date</th>
                                <th>Project</th>
                                <th>Purpose</th>
                                <th class="text-end">Debit (Dr)</th>
                                <th class="text-end">Credit (Cr)</th>
                                <th class="text-end">Closing</th>
                            </tr>
                        </thead>

                        <tbody>
                            @php
                                $totalDebit = 0;
                                $totalCredit = 0;
                            @endphp

                            @php 
                                $opening = $ledgers->opening_balance ?? 0; 
                            @endphp

                            @if($projectId)
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end text-primary">
                                    Opening Balance
                                </td>
                                <td class="text-end">
                                    {{ number_format(abs($opening),2) }}
                                    {{ $opening >= 0 ? 'Dr' : 'Cr' }}
                                </td>
                            </tr>
                            @endif

                            @foreach($ledgers as $ledger)
                                @php
                                    $totalDebit += $ledger->debit;
                                    $totalCredit += $ledger->credit;
                                @endphp

                                <tr>
                                    <td class="text-nowrap">
                                        {{ \Carbon\Carbon::parse($ledger->entry_date)->format('d M Y') }}
                                    </td>

                                    <td class="fw-semibold">
                                        {{ ucwords(optional($ledger->project)->project_name) ?? '-' }}
                                    </td>

                                    <td>
                                        <span class="badge rounded-pill
                                            @if($ledger->type === 'invoice') bg-warning text-dark
                                            @elseif($ledger->type === 'payment') bg-success
                                            @else bg-secondary
                                            @endif
                                        ">
                                            {{ ucfirst($ledger->type) }}
                                        </span>
                                    </td>

                                    <td class="text-end text-danger fw-semibold">
                                        {{ $ledger->debit > 0 ? number_format($ledger->debit,2) : '-' }}
                                    </td>

                                    <td class="text-end text-success fw-semibold">
                                        {{ $ledger->credit > 0 ? number_format($ledger->credit,2) : '-' }}
                                    </td>

                                    <td class="text-end fw-bold">
                                        {{-- {{ number_format(abs($ledger->balance),2) }}
                                        <span class="text-muted">
                                            {{ $ledger->balance > 0 ? 'Dr' : 'Cr' }}
                                        </span> --}}
                                        {{ number_format(abs($ledger->running_balance),2) }}
                                        <span class="text-muted">
                                            {{-- {{ $ledger->running_balance >= 0 ? 'Dr' : 'Cr' }} --}}
                                            {{ $ledger->running_balance >= 0 ? 'Dr' : 'Cr' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                        {{-- Totals --}}
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="3" class="text-end">Total</td>
                                <td class="text-end text-danger">
                                    {{ number_format($totalDebit,2) }}
                                </td>
                                <td class="text-end text-success">
                                    {{ number_format($totalCredit,2) }}
                                </td>
                                <td class="text-end">
                                    @php $closing = $ledgers->last()->running_balance; @endphp
                                    {{ number_format(abs($closing),2) }}
                                    {{ $closing >= 0 ? 'Dr' : 'Cr' }}
                                    {{-- {{ number_format(abs($ledgers->last()->balance),2) }} --}}
                                    {{-- {{ $ledgers->last()->balance > 0 ? 'Dr' : 'Cr' }} --}}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @else
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                <h6 class="mb-1">No Ledger Data</h6>
                <p class="mb-0">Please select a client or project to view ledger entries</p>
            </div>
        </div>
    @endif
</div>
 