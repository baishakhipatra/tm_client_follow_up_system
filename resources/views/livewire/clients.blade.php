<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-semibold mb-0">Clients</h3>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <input
                type="text"
                class="form-control"
                placeholder="Search by Name,Company Name, Email, Phone Number"
                wire:model.live.debounce.500ms="search"
                style="width: 280px;"
            >
            <button
                wire:click="openModal"
                class="btn btn-primary d-flex align-items-center gap-2">
                <span class="fs-5">+</span> Add Client
            </button>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Client</th>
                            <th>Contact</th>
                            <th>GST/Tax ID</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($clients as $client)
                            <tr wire:key="client-{{ $client->id }}">
                                <td class="fw-medium">{{ ucwords($client->client_name) }}</td>
                                <td>{{ $client->phone_number }}</td>
                                <td>{{ $client->gst ?? '-' }}</td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox"
                                            wire:click="toggleStatus({{ $client->id }})"
                                            {{ $client->status ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td>{{ $client->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary"
                                            wire:click="editClient({{ $client->id }})">
                                           <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                            wire:click="confirmDelete({{ $client->id }})">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    No clients found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">
                    {{ $clients->links() }}
                </div>
            </div>
        </div>
    </div>
    @if($showModal)
        <div wire:ignore.self class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5)">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ $isEdit ? 'Edit Client' : 'Add New Client' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeModal"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model.defer="client_name" class="form-control">
                                @error('client_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Company Name <span class="text-danger">*</span></label>
                                <input type="text" wire:model.defer="company_name" class="form-control">
                                @error('company_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Primary Email <span class="text-danger">*</span></label>
                                <input type="email" wire:model.defer="primary_email" class="form-control">
                                @error('primary_email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Secondary Email</label>
                                <input type="email" wire:model.defer="secondary_email" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="text" wire:model.defer="phone_number" class="form-control">
                                @error('phone_number')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">GST / Tax ID</label>
                                <input type="text" wire:model.defer="gst" class="form-control">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Billing Address <span class="text-danger">*</span></label>
                                <textarea wire:model.defer="billing_address" class="form-control" rows="3"></textarea>
                                @error('billing_address')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-outline-secondary" wire:click="closeModal">
                            Cancel
                        </button>
                        <button class="btn btn-primary" wire:click="saveClient">
                            {{ $isEdit ? 'Update Client' : 'Create Client' }}
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


