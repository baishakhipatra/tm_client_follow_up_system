<div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="col-md-4">

            <div class="card shadow-sm">
                <div class="card-header text-center bg-dark text-white">
                    <h5 class="mb-0">Admin Login</h5>
                </div>

                <div class="card-body">
                    <form wire:submit.prevent="login">
                        {{-- Email --}}
                        <div class="mb-3">
                            <label class="form-label">Email address</label>
                            <input
                                type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                wire:model.defer="email"
                                placeholder="Enter email"
                            >

                            @error('email')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Password --}}
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input
                                type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                wire:model.defer="password"
                                placeholder="Enter password"
                            >

                            @error('password')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Submit --}}
                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark">
                                Login
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
