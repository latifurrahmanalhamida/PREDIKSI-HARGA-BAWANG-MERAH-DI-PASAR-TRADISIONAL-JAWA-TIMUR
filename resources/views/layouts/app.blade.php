<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Prediksi Harga Bawang Merah')</title>
    
    <!-- Bootstrap CSS -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo4.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    
    <!-- Custom CSS -->
    <!-- <link rel="stylesheet" href="{{ asset('css/app.css') }}"> -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=2">
    @yield('styles')
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid px-4">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-graph-up"></i> Prediksi Bawang Merah
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav ms-auto">
                    <a class="nav-link" href="{{ route('dashboard') }}">
                        <i class="bi bi-house"></i> Dashboard
                    </a>
                    <a class="nav-link" href="{{ route('prediction.form') }}">
                        <i class="bi bi-calculator"></i> Prediksi
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="container-fluid px-4 py-3">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-light text-center py-3 mt-4">
        <div class="container-fluid">
            <p class="mb-0 text-muted">
                 Sistem Prediksi Harga Bawang Merah 
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min. js"></script>
    
    <!-- Custom JS -->
    <script src="{{ asset('js/app.js') }}"></script>
    @yield('scripts')
</body>
</html>