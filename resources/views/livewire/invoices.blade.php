<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h3 class="fw-semibold mb-0">Vouchers</h3>
            <small class="text-muted">Manage and track all vouchers</small>
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
            <button wire:click="openModal"
                    class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-lg"></i>
                Add Voucher
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
                            <th>Voucher</th>
                            <th>Client</th>
                            <th>Payment Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($payments as $payment)
                            <tr wire:key="payment-{{ $payment->id }}">

                                <td class="fw-medium">
                                    {{ strtoupper($payment->voucher_no) }}
                                </td>

                                <td>
                                    {{ ucwords($payment->client->client_name ?? '-') }} <br>
                                    Company: {{ ucwords($payment->client->company_name ?? '') }}
                                </td>

                                <td>
                                    {{ number_format($payment->payment_amount, 2) }}
                                </td>

                                <td>
                                    {{ ucfirst($payment->payment_method) }}
                                </td>

                                <td>
                                    @if($payment->status == 1)
                                        <span class="badge bg-success">Paid</span>
                                    @else
                                        <span class="badge bg-danger">Unpaid</span>
                                    @endif
                                </td>

                                <td>
                                    @if($payment->status != 1)
                                        <button
                                            class="btn btn-sm btn-outline-warning"
                                            wire:click="editVoucher({{ $payment->id }})"
                                            title="Edit Voucher">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endif
                                    @if($payment->status != 1)
                                        <button
                                            class="btn btn-sm btn-outline-success"
                                            wire:click="openPayModal({{ $payment->id }})">
                                            <i class="bi bi-cash-coin"></i>
                                        </button>
                                    @endif

                                    <button
                                        class="btn btn-sm btn-outline-info"
                                        wire:click="viewVoucher({{ $payment->id }})"
                                    >
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    No unpaid vouchers found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
                <div class="mt-3">
                    {{ $invoices->links() }}
                </div>
            </div>
            @if($showViewVoucherModal)
                <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5)">
                    <div class="modal-dialog modal-xl modal-dialog-centered">
                        <div class="modal-content">

                            <div class="modal-header">
                                <h5 class="modal-title">
                                    Voucher Details – {{ $viewVoucherData->voucher_no }}
                                </h5>
                                <button type="button" class="btn-close"
                                        wire:click="$set('showViewVoucherModal', false)">
                                </button>
                            </div>

                            <div class="modal-body">
                                {{-- Voucher Info --}}
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <strong>Client:</strong><br>
                                        {{ $viewVoucherData->client->client_name ?? '-' }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Total Amount:</strong><br>
                                        ₹{{ number_format($viewVoucherData->payment_amount, 2) }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Status:</strong><br>
                                        @if($viewVoucherData->status == 1)
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <span class="badge bg-danger">Unpaid</span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Invoice Breakdown --}}
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Invoice No</th>
                                                <th>Voucher Amount</th>
                                                <th>Paid Amount</th>
                                                <th>Remaining</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($viewVoucherInvoices as $item)
                                                <tr>
                                                    <td>{{ $item->invoice->invoice_number }}</td>
                                                    <td>₹{{ number_format($item->invoice_amount, 2) }}</td>
                                                    <td>₹{{ number_format($item->paid_amount, 2) }}</td>
                                                    <td>₹{{ number_format($item->rest_amount, 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">
                                                        No invoices linked
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button class="btn btn-secondary"
                                        wire:click="$set('showViewVoucherModal', false)">
                                    Close
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($showModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5)">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $isEdit ? 'Edit Voucher' : 'Add New Voucher' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">
                                    Voucher Number <span class="text-danger">*</span>
                                </label>

                                <input
                                    type="text"
                                    class="form-control"
                                    wire:model.defer="invoice_number"
                                    readonly
                                >

                                @error('invoice_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>


                            <div class="col-md-12">
                                <label class="form-label">Client <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="client_id" wire:change="ChangeclientId($event.target.value)">
                                    <option value="">Select a Client</option>
                                        @foreach($clientsList as $client)
                                            <option value="{{ $client->id }}">
                                                {{ucwords($client->client_name)}}
                                            </option>
                                        @endforeach
                                </select>
                                @error('client_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Total Due Amount</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    value="₹ {{ number_format($total_due, 2) }}"
                                    readonly
                                >
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Amount</label>
                                <input type="number" class="form-control"
                                    wire:model.defer="payment_amount">
                                    @error('payment_amount')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Voucher Date <span class="text-danger">*</span></label>
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
                        <button class="btn btn-primary" wire:click="saveVoucher">
                            {{ $isEdit ? 'Update Voucher' : 'Create Voucher' }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

    @if($showPaymentModal)
        <div class="modal fade show d-block" style="background: rgba(0,0,0,.5)">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">Invoice Details</h5>
                        <button class="btn-close" wire:click="$set('showPaymentModal', false)"></button>
                    </div>

                    <div class="modal-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Invoice No</th>
                                    <th>Due Amount</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($clientInvoices as $invoice)
                                    <tr>
                                        <td>{{ $invoice->invoice_number }} - {{ucwords(optional($invoice->project)->project_name)}}</td>
                                        <td>{{ number_format($invoice->required_payment_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="alert alert-info mt-3">
                            <strong>Voucher Amount:</strong>
                            ₹{{ number_format($this->voucherAmount, 2) }} <br>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary"
                                wire:click="$set('showPaymentModal', false)">
                            Cancel
                        </button>

                        <button class="btn btn-success"
                                wire:click="storeVoucherPayment">
                            Confirm Payment
                        </button>
                    </div>

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
