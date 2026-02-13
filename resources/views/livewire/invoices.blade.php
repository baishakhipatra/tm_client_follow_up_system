<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-semibold mb-0">Invoices</h3>
            <small class="text-muted">Manage and track all invoices</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle d-flex align-items-center gap-1"
                        type="button"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
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
            <button wire:click="openInvoiceTypeModal"
                    class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-lg"></i>
                Add Invoice
            </button>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">

                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control"
                        wire:model.live="filter_from_date">
                </div>

                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control"
                        wire:model.live="filter_to_date">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Company</label>
                    <select class="form-select"
                        wire:model.live="filter_client">
                        <option value="">All Company</option>
                        @foreach($clientsList as $client)
                            <option value="{{ $client->id }}">
                                {{ ucwords($client->company_name) }} ({{ucwords($client->client_name)}})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Project</label>
                    <select class="form-select"
                        wire:model.live="filter_project">
                        <option value="">All Projects</option>
                        @foreach($projectsList as $project)
                            <option value="{{ $project->id }}">
                                {{ ucwords($project->project_name) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12 d-flex justify-content-end">
                    <button 
                        type="button"
                        class="btn btn-outline-secondary btn-sm"
                        wire:click="resetFilters"
                        title="Reset Filters"
                    >
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Invoice</th>
                            <th>Client</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Received</th>
                            <th>Pending</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($invoices as $invoice)
                            <tr wire:key="invoice-{{ $invoice->id }}">
                                <td class="fw-medium">{{ strtoupper($invoice->invoice_number) }}</td>
                                <td>{{ ucwords($invoice->client->client_name) ?? '-' }}<br>Project: {{ucwords(optional($invoice->project)->project_name)}} <br>Company: {{ ucwords($invoice->client->company_name ?? '') }} </td>
                                <td>{{ $invoice->invoice_date ?? '-' }}</td>
                                <td>{{ $invoice->due_date ?? '-' }}</td>
                                <td>{{ $invoice->amount ?? '-' }}</td>
                                <td>{{ $invoice->paid_amount }}</td>
                                <td>{{ $invoice->pending_amount ?? '-' }}</td>
                                <td>
                                    @if($invoice->status === 'paid')
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle me-1"></i> Paid
                                        </span>
                                    @elseif($invoice->status === 'partially_paid')
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-hourglass-split me-1"></i> Partially Paid
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i> Unpaid
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if($invoice->status !== 'paid')
                                        <button
                                            class="btn btn-sm btn-outline-success"
                                            wire:click="openPaymentModal({{ $invoice->id }})"
                                            title="Record Payment"
                                        >
                                            <i class="bi bi-cash-coin"></i>
                                        </button>
                                    @else
                                        <button
                                            class="btn btn-sm btn-outline-info"
                                            wire:click="viewPaymentDetails({{ $invoice->id }})"
                                            title="View Payment Details"
                                        >
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    @endif
                                    <button
                                        class="btn btn-sm btn-outline-primary"
                                        wire:click="editInvoice({{ $invoice->id }})"
                                    >
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    No invoices found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
    @if($showInvoiceTypeModal)
    <div class="modal fade show d-block" tabindex="-1"
        style="background: rgba(0,0,0,.5)">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow">

                <div class="modal-header">
                    <h5 class="modal-title">Select Invoice Type</h5>
                    <button type="button" class="btn-close"
                            wire:click="$set('showInvoiceTypeModal', false)"></button>
                </div>

                <div class="modal-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input"
                            type="radio"
                            wire:model="invoice_type"
                            value="tax"
                            id="taxInvoice">
                        <label class="form-check-label" for="taxInvoice">
                            Tax Invoice
                        </label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input"
                            type="radio"
                            wire:model="invoice_type"
                            value="non_tax"
                            id="nonTaxInvoice">
                        <label class="form-check-label" for="nonTaxInvoice">
                            Without Tax Invoice
                        </label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-outline-secondary"
                            wire:click="$set('showInvoiceTypeModal', false)">
                        Cancel
                    </button>
                    <button class="btn btn-primary"
                            wire:click="proceedToInvoiceForm">
                        Continue
                    </button>
                </div>

            </div>
        </div>
    </div>
    @endif

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5)">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $isEdit ? 'Edit Invoice' : 'Add New Invoice' }}
                            <span class="badge bg-info ms-2">
                                {{ $invoice_type === 'tax' ? 'Tax Invoice' : 'Without Tax' }}
                            </span>
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">
                                    Invoice Number <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    wire:model.defer="invoice_number"
                                    @if($invoice_type === 'non_tax') readonly @endif
                                >

                                @error('invoice_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>


                            <div class="col-md-12">
                                <label class="form-label">Projects <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model.live="project_id">
                                    <option value="">Select a project</option>
                                    @foreach($projectsList as $project)
                                        <option value="{{ $project->id }}">
                                            {{ucwords($project->client->client_name)}} - {{ucwords($project->project_name)}}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Project Due</label>
                                <input type="number" class="form-control" readonly
                                    value="{{ $project_due }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Amount</label>
                                <input type="number" class="form-control"
                                    wire:model.defer="amount">
                                    @error('amount')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Invoice Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control"
                                    wire:model.defer="invoice_date">
                                    @error('invoice_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Due Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control"
                                    wire:model.defer="due_date">
                                    @error('due_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-outline-secondary" wire:click="closeModal">
                            Cancel
                        </button>
                        <button class="btn btn-primary" wire:click="saveInvoices">
                            {{ $isEdit ? 'Update Invoice' : 'Create Invoice' }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

    @if($showPaymentModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5)">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                <form wire:submit.prevent="storePayment">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $isViewMode ? 'Payment Details' : 'Record Payment' }}
                        </h5>

                        <button type="button" class="btn-close" wire:click="$set('showPaymentModal', false)"></button>
                    </div>

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Invoice *</label>
                            <input type="text" class="form-control" disabled
                                value="{{ strtoupper($selectedInvoice->invoice_number ?? '') }}">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Invoiced Amount *</label>
                                <input type="number" step="0.01" class="form-control"
                                    wire:model.defer="payment_amount"  @if($isViewMode) disabled @endif>
                                @error('payment_amount') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Date *</label>
                                <input type="date" class="form-control"
                                    wire:model.defer="payment_date"  @if($isViewMode) disabled @endif>
                                @error('payment_date') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" wire:model.defer="payment_method"  @if($isViewMode) disabled @endif>
                                <option value="">Select method</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="upi">UPI</option>
                                <option value="card">Card</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control"
                                wire:model.defer="payment_notes" @if($isViewMode) disabled @endif
                                rows="3"
                                placeholder="Optional payment notes..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="$set('showPaymentModal', false)">
                            Cancel
                        </button>
                        @if(!$isViewMode)
                            <button type="submit" class="btn btn-primary">
                                Record Payment
                            </button>
                        @endif
                    </div>
                </form>
                </div>
            </div>
        </div>
    @endif

</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.addEventListener('showConfirm', function (event) {
            let itemId = event.detail[0].itemId;
            Swal.fire({
                title: "Delete Client?",
                text: "Are you sure you want to delete this client?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, Delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    @this.call('delete', itemId);
                }
            });
        });
    </script>
@endpush
