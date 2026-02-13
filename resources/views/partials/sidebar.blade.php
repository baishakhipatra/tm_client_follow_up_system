<div class="sidebar bg-dark text-white">
    <div class="d-flex flex-column flex-shrink-0 p-3 text-white bg-dark"
        style="width:260px; min-height:100vh;">

        {{-- Logo --}}
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center mb-4 text-white text-decoration-none">
            <img src="{{ asset('logo.png') }}" height="55" class="me-2">
        </a>

        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item mb-2">
                <a href="{{ route('dashboard') }}"
                class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house me-2"></i> Dashboard
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('admin.clients.index') }}"
                class="nav-link {{ request()->routeIs('admin.clients.index') ? 'active' : '' }}">
                    <i class="bi bi-people me-2"></i> Clients
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('admin.projects.index') }}"
                class="nav-link {{ request()->routeIs('admin.projects.index') ? 'active' : '' }}">
                    <i class="bi bi-folder me-2"></i> Projects
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('admin.invoices.index') }}"
                class="nav-link {{ request()->routeIs('admin.invoices.index') ? 'active' : '' }}">
                    <i class="bi bi-receipt me-2"></i> Invoices
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('admin.ledger.index') }}"
                class="nav-link {{ request()->routeIs('admin.ledger.index') ? 'active' : '' }}">
                    <i class="bi bi-receipt me-2"></i> Ledger
                </a>
            </li>
        </ul>

        <hr>

        {{-- User Info --}}
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button class="btn btn-outline-light btn-sm w-100">
                Sign out
            </button>
        </form>
    </div>
</div>
