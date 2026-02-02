<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>TM Client Follow Up System</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    @livewireStyles
</head>
<body class="bg-light">

    <div class="container-fluid">
        <div class="row min-vh-100">

            <div class="col-md-2 p-0">
                @include('partials.sidebar')
            </div>

            <div class="col-md-10 p-0">
                @include('partials.header')

                <main class="p-4">
                    {{ $slot }}
                </main>
            </div>

        </div>
    </div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

@livewireScripts
    <script>
        document.addEventListener('livewire:init', () => {

            Livewire.on('toastr', (data) => {
                toastr.options = {
                    "closeButton": true,
                    "progressBar": true,
                    "timeOut": "3000",
                    "positionClass": "toast-top-right"
                };

                if (data.type === 'success') {
                    toastr.success(data.message);
                } else if (data.type === 'error') {
                    toastr.error(data.message);
                } else if (data.type === 'warning') {
                    toastr.warning(data.message);
                } else {
                    toastr.info(data.message);
                }
            });

        });
    </script>

    @stack('scripts')
</body>
</html>
