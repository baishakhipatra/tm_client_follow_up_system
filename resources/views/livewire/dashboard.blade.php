<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="mb-4">
        <h3 class="fw-semibold mb-1">Dashboard</h3>
        <p class="text-muted">Overview of your payment tracking and client management</p>
    </div>

    {{-- STAT CARDS --}}
    <div class="row g-4 mb-4">

        {{-- Total Clients --}}
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 bg-light">
                <div class="card-body">
                    <p class="text-muted mb-1">Total Clients</p>
                    <h2 class="fw-bold">{{ $totalClients }}</h2>
                    <small class="text-muted">Active clients</small>
                </div>
            </div>
        </div>

        {{-- projects--}}
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 bg-warning-subtle">
                <div class="card-body">
                    <p class="text-muted mb-1">Projects</p>
                    <h2 class="fw-bold">{{ $projects }}</h2>
                </div>
            </div>
        </div>

        {{-- Overdue Invoices --}}
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 bg-danger-subtle">
                <div class="card-body">
                    <p class="text-muted mb-1">Overdue Invoices</p>
                    <h2 class="fw-bold">{{ $overdueInvoices }}</h2>
                    <small class="text-muted">Require attention</small>
                </div>
            </div>
        </div>

        {{-- Payments Today --}}
        <div class="col-md-3">
            <div class="card shadow-sm border-0 h-100 bg-success-subtle">
                <div class="card-body">
                    <p class="text-muted mb-1">Payments Today</p>
                    <h2 class="fw-bold">₹{{ number_format($paymentsToday, 2) }}</h2>
                    <small class="text-muted">{{ now()->format('d M Y') }}</small>
                </div>
            </div>
        </div>

    </div>

    {{-- PENDING INVOICES --}}
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-semibold mb-0">Pending Invoices</h5>
                <a href="{{ route('admin.invoices.index') }}" class="text-primary">View all</a>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingInvoices as $invoice)
                            <tr>
                                <td>#{{ $invoice->invoice_number }}</td>
                                <td>{{ $invoice->created_at->format('d M Y') }}</td>
                                <td>₹{{ number_format($invoice->required_payment_amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-warning">
                                        {{ $invoice->status == 1 ? 'Partially Paid' : 'Pending' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No pending invoices
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</div>
