<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-semibold mb-0">Projects</h3>
        </div>

        <div class="d-flex gap-2 align-items-center">
            <input
                type="text"
                class="form-control"
                placeholder="Search projects"
                wire:model.live.debounce.500ms="search"
                style="width: 280px;"
            >
            <button
                wire:click="openModal"
                class="btn btn-primary d-flex align-items-center gap-2">
                <span class="fs-5">+</span> Add Project
            </button>

        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Project</th>
                            <th>Client</th>
                            <th>Project Cost</th>
                            <th>Duration</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($projects as $project)
                            <tr wire:key="project-{{ $project->id }}">
                                <td>{{ ucwords($project->project_name) }}<br>Code: {{ $project->project_code ?? '-' }}</td>
                                <td>
                                    {{ ucwords(optional($project->client)->client_name ?? '-') }} <br>
                                    Company: {{ ucwords(optional($project->client)->company_name ?? '-') }}
                                </td>

                                <td>{{ $project->project_cost ?? '-' }}</td>
                                <td>Start-date: {{ $project->start_date ?? '-' }} <br> End-date: {{ $project->end_date ?? '-' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-outline-primary"
                                            wire:click="editProject({{ $project->id }})">
                                           <i class="bi bi-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                            wire:click="confirmDelete({{ $project->id }})">
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
                    {{ $projects->links() }}
                </div>
            </div>
        </div>
    </div>
    @if($showModal)
    <div wire:ignore.self class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,.5)">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">

                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ $isEdit ? 'Edit Project' : 'Add New Project' }}
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeModal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">

                        {{-- Project Name --}}
                        <div class="col-md-6">
                            <label class="form-label">Project Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control"
                                wire:model.defer="project_name">
                                @error('project_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                        </div>

                        {{-- Project Code --}}
                        <div class="col-md-6">
                            <label class="form-label">Project Code</label>
                            <input type="text" class="form-control"
                                wire:model.defer="project_code">
                        </div>

                        {{-- Client --}}
                        <div class="col-md-6">
                            <label class="form-label">Client <span class="text-danger">*</span></label>
                            <select class="form-select" wire:model.defer="client_id">
                                <option value="hidden">Select a client</option>
                                @foreach($clientsList as $client)
                                    <option value="{{ $client->id }}">
                                        {{ucwords($client->company_name)}}({{ ucwords($client->client_name) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Total Cost --}}
                        <div class="col-md-6">
                            <label class="form-label">Total Project Cost <span class="text-danger">*</span></label>
                            <input type="number" class="form-control"
                                wire:model.defer="project_cost">
                                @error('project_cost')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Payment Terms</label>
                            <input type="text" class="form-control"
                                placeholder="e.g. Net 30"
                                wire:model.defer="payment_terms">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control"
                                wire:model.defer="start_date">
                                @error('start_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">End Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control"
                                wire:model.defer="end_date">
                                @error('end_date')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" wire:click="closeModal">
                        Cancel
                    </button>
                    <button class="btn btn-primary" wire:click="saveProject">
                        {{ $isEdit ? 'Update Project' : 'Create Project' }}
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
                title: "Delete Project?",
                text: "Are you sure you want to delete this project?",
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


