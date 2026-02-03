<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-semibold mb-0">Invoices</h3>
        </div>
        <div class="d-flex gap-2 align-items-center">
            {{-- <input
                type="text"
                class="form-control"
                placeholder="Search invoices"
                wire:model.live.debounce.500ms="search"
                style="width: 280px;"
            > --}}
            <button
                wire:click="openInvoiceTypeModal"
                class="btn btn-primary d-flex align-items-center gap-2">
                <span class="fs-5">+</span> Add Invoice
            </button>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
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
                                <td class="fw-medium">{{ strtoupper($invoice->invoice_number) }}<br>{{ ucwords($invoice->client->company_name ?? '') }}</td>
                                <td>{{ ucwords($invoice->client->client_name) ?? '-' }}<br>{{ucwords($invoice->project->project_name)}}</td>
                                <td>{{ $invoice->invoice_date ?? '-' }}</td>
                                <td>{{ $invoice->due_date ?? '-' }}</td>
                                <td>{{ $invoice->amount ?? '-' }}</td>
                                <td>{{ $invoice->paid_amount }}</td>
                                <td>{{ $invoice->pending_amount ?? '-' }}</td>
                                <td>
                                    {{ ucwords(str_replace('_', ' ', $invoice->status)) }}
                                </td>

                                <td>
                                    <button
                                        class="btn btn-sm btn-outline-success"
                                        wire:click="openPaymentModal({{ $invoice->id }})"
                                        title="Record Payment"
                                    >
                                        <i class="bi bi-cash-coin"></i>
                                    </button>
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
                {{-- <div class="mt-3">
                    {{ $invoices->links() }}
                </div> --}}
            </div>
        </div>
    </div>
    @if($showInvoiceTypeModal)
    <div wire:ignore.self class="modal fade show d-block" tabindex="-1"
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
        <div wire:ignore.self class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5)">
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

                            <div class="col-md-6">
                                <label class="form-label">Amount</label>
                                <input type="number" class="form-control"
                                    wire:model.defer="amount">
                                    @error('amount')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Projects <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model.defer="project_id">
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
                        <h5 class="modal-title">Record Payment</h5>
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
                                <label class="form-label">Amount *</label>
                                <input type="number" step="0.01" class="form-control"
                                    wire:model.defer="payment_amount">
                                @error('payment_amount') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Date *</label>
                                <input type="date" class="form-control"
                                    wire:model.defer="payment_date">
                                @error('payment_date') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" wire:model.defer="payment_method">
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
                                wire:model.defer="payment_notes"
                                rows="3"
                                placeholder="Optional payment notes..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-outline-secondary" wire:click="$set('showPaymentModal', false)">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Record Payment
                        </button>
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
