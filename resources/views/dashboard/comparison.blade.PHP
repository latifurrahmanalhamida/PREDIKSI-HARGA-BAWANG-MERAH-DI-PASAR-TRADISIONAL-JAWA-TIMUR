@extends('layouts.app')

@section('title', 'Perbandingan Semua Wilayah - Dashboard')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<style>
. comparison-table {
    font-size: 0.95rem;
}
.trend-cell {
    text-align: center;
}
.price-cell {
    font-weight: 600;
    color: #2c5aa0;
}
.region-cell {
    font-weight: 600;
}
.prediction-count {
    font-size: 0.8rem;
    color: #6c757d;
}
</style>
@endsection

@section('content')
<div class="fade-in">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3><i class="bi bi-bar-chart-line"></i> Perbandingan Semua Wilayah</h3>
                            <p class="text-muted mb-0">Analisis prediksi harga bawang merah untuk 9 wilayah di Jawa Timur</p>
                        </div>
                        <div>
                            <button class="btn btn-primary" onclick="refreshComparison()">
                                <i class="bi bi-arrow-clockwise"></i> Refresh Data
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Table -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="bi bi-table"></i> Data Perbandingan Wilayah</h5>
                    <div>
                        <button class="btn btn-success btn-sm" onclick="exportToExcel()">
                            <i class="bi bi-file-earmark-excel"></i> Export Excel
                        </button>
                        <button class="btn btn-info btn-sm" onclick="exportToPDF()">
                            <i class="bi bi-file-earmark-pdf"></i> Export PDF
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover comparison-table" id="comparisonTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Wilayah</th>
                                    <th>Harga Rata-rata</th>
                                    <th>Prediksi Besok</th>
                                    <th>Trend</th>
                                    <th>Perubahan (%)</th>
                                    <th>Confidence</th>
                                    <th>Total Prediksi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($comparisons as $index => $data)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="region-cell">
                                        <i class="bi bi-geo-alt text-primary"></i>
                                        {{ $data['region'] }}
                                    </td>
                                    <td class="price-cell">
                                        Rp {{ number_format($data['avg_price'], 0, ',', '.') }}
                                    </td>
                                    <td class="price-cell">
                                        <strong>Rp {{ number_format($data['prediction'], 0, ',', '.') }}</strong>
                                    </td>
                                    <td class="trend-cell">
                                        @if($data['trend'] == 'up')
                                            <span class="badge bg-success">
                                                <i class="bi bi-arrow-up"></i> Naik
                                            </span>
                                        @elseif($data['trend'] == 'down')
                                            <span class="badge bg-danger">
                                                <i class="bi bi-arrow-down"></i> Turun
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="bi bi-dash"></i> Stabil
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $change = round((($data['prediction'] - $data['avg_price']) / $data['avg_price']) * 100, 2);
                                        @endphp
                                        <span class="fw-bold {{ $change > 0 ? 'text-success' : ($change < 0 ? 'text-danger' : 'text-muted') }}">
                                            {{ $change > 0 ? '+' : '' }}{{ $change }}%
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $data['confidence'] }}%</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="prediction-count">{{ $data['total_predictions'] }}</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-outline-primary btn-sm" 
                                                onclick="viewDetails('{{ $data['region'] }}')">
                                            <i class="bi bi-eye"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-up-circle text-success fs-1"></i>
                    <h3 class="text-success">{{ $statistics['trending_up'] }}</h3>
                    <p class="mb-0">Wilayah Trend Naik</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <i class="bi bi-arrow-down-circle text-danger fs-1"></i>
                    <h3 class="text-danger">{{ $statistics['trending_down'] }}</h3>
                    <p class="mb-0">Wilayah Trend Turun</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <i class="bi bi-currency-dollar text-info fs-1"></i>
                    <h3 class="text-info">Rp {{ number_format($statistics['avg_prediction'], 0, ',', '.') }}</h3>
                    <p class="mb-0">Rata-rata Prediksi</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <i class="bi bi-bullseye text-warning fs-1"></i>
                    <h3 class="text-warning">{{ $statistics['avg_confidence'] }}%</h3>
                    <p class="mb-0">Rata-rata Confidence</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparison Chart -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-graph-up"></i> Visualisasi Perbandingan Prediksi</h5>
                </div>
                <div class="card-body">
                    <canvas id="comparisonChart" width="400" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Comparison Chart
const comparisonData = @json($comparisons);
const ctx = document.getElementById('comparisonChart').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels:  comparisonData.map(item => item.region),
        datasets: [
            {
                label: 'Harga Rata-rata',
                data: comparisonData.map(item => item. avg_price),
                backgroundColor:  'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            },
            {
                label: 'Prediksi Besok',
                data: comparisonData.map(item => item.prediction),
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value. toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});

// Functions
function refreshComparison() {
    location.reload();
}

function viewDetails(region) {
    window.open(`/dashboard?region=${region}`, '_blank');
}

function exportToExcel() {
    // TODO: Implement Excel export
    alert('Fitur export Excel akan segera tersedia! ');
}

function exportToPDF() {
    // TODO: Implement PDF export
    alert('Fitur export PDF akan segera tersedia!');
}
</script>
@endsection