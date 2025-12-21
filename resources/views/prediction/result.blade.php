@extends('layouts.app')

@section('title', 'Hasil Prediksi - Prediksi Bawang Merah')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/prediction.css') }}">
@endsection

@section('content')
<div class="result-container fade-in">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <!-- Success Alert -->
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i>
                <strong>Prediksi berhasil!</strong> Hasil prediksi untuk wilayah {{ $result['region'] }} telah selesai.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>

            <div class="row">
                <!-- Input Data -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <h5><i class="bi bi-list-ol"></i> Data Input</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong><i class="bi bi-geo-alt"></i> Wilayah:</strong>
                                <span class="badge bg-primary ms-2">{{ $result['region'] }}</span>
                            </div>
                            
                            <strong><i class="bi bi-graph-up"></i> Data 7 hari terakhir:</strong>
                            <div class="mt-2">
                                <canvas id="inputChart" width="400" height="200"></canvas>
                            </div>
                            
                            <div class="mt-3">
                                <small class="text-muted">
                                    @for ($i = 1; $i <= 7; $i++)
                                        <div>Hari {{ $i }}:  Rp {{ number_format($input['day' . $i], 0, ',', '.') }}</div>
                                    @endfor
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prediction Result -->
                <div class="col-md-6 mb-4">
                    <div class="card h-100 border-success">
                        <div class="card-header bg-success text-white">
                            <h5><i class="bi bi-bullseye"></i> Hasil Prediksi</h5>
                        </div>
                        <div class="card-body prediction-result text-center">
                            @php
                                $lastPrice = $input['day7'];
                                $prediction = $result['prediction'];
                                $trend = $prediction > $lastPrice ? 'up' : ($prediction < $lastPrice ? 'down' : 'stable');
                                $trendPercent = $lastPrice > 0 ? round((($prediction - $lastPrice) / $lastPrice) * 100, 2) : 0;
                            @endphp
                            
                            <div class="mb-5">
                                <h1 class="text-white mb-2">
                                    Rp {{ number_format($result['prediction'], 0, ',', '. ') }}
                                </h1>
                                <p class="text-white mb-0">Prediksi harga besok</p>
                                <small class="text-white">
                                    {{ \Carbon\Carbon::parse($result['predicted_for_date'] ?? now()->addDay())->locale('id')->translatedFormat('d F Y') }}
                                </small>
                            </div>
                            
                            <!-- Model Stats -->
                            <div class="model-info mt-4">
                                <div class="row text-center">
                                    <div class="col-4 model-stat">
                                        <h6>WINDOW</h6>
                                        <strong>{{ $result['window_size'] ?? 7 }} hari</strong>
                                    </div>
                                    <div class="col-4 model-stat">
                                        <h6>RUNTIME</h6>
                                        <strong>{{ number_format($result['runtime_ms'] ?? 0, 0) }} ms</strong>
                                    </div>
                                    <div class="col-4 model-stat">
                                        <h6>TREND</h6>
                                        <strong>
                                            @if($trend == 'up')
                                                <span class="text-success">↑ {{ abs($trendPercent) }}%</span>
                                            @elseif($trend == 'down')
                                                <span class="text-danger">↓ {{ abs($trendPercent) }}%</span>
                                            @else
                                                <span class="text-secondary">→ 0%</span>
                                            @endif
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Combined Chart -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="bi bi-graph-up"></i> Visualisasi Trend + Prediksi</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="combinedChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <div class="d-grid gap-2 d-md-flex justify-content-md-between">
                    <div>
                        <a href="{{ route('prediction.form') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Prediksi Lagi
                        </a>
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-house"></i> Dashboard
                        </a>
                    </div>
                    <div>
                        <button class="btn btn-success btn-download" onclick="downloadResult()">
                            <i class="bi bi-download"></i> Download PDF
                        </button>
                        <button class="btn btn-outline-info btn-share" onclick="shareResult()">
                            <i class="bi bi-share"></i> Share
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Data untuk chart
const inputData = [
    @for($i = 1; $i <= 7; $i++)
        {{ $input['day' . $i] }}{{ $i < 7 ? ',' : '' }}
    @endfor
];
const predictionValue = {{ $result['prediction'] }};

// Input Chart
const inputCtx = document.getElementById('inputChart').getContext('2d');
new Chart(inputCtx, {
    type: 'line',
    data: {
        labels:  ['H-6', 'H-5', 'H-4', 'H-3', 'H-2', 'H-1', 'Kemarin'],
        datasets: [{
            label: 'Harga Input',
            data: inputData,
            borderColor: '#6c757d',
            backgroundColor:  'rgba(108, 117, 125, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
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

// Combined Chart
const combinedCtx = document.getElementById('combinedChart').getContext('2d');
new Chart(combinedCtx, {
    type: 'line',
    data: {
        labels: ['H-6', 'H-5', 'H-4', 'H-3', 'H-2', 'H-1', 'Kemarin', 'Prediksi Besok'],
        datasets: [{
            label: 'Data Historis',
            data: [... inputData, null],
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: false
        }, {
            label:  'Prediksi',
            data: [null, null, null, null, null, null, inputData[6], predictionValue],
            borderColor:  '#198754',
            backgroundColor: 'rgba(25, 135, 84, 0.1)',
            borderDash: [5, 5],
            tension: 0.4,
            fill: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { 
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: false,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});

function downloadResult() {
    alert('Fitur download PDF akan segera tersedia!');
}

function shareResult() {
    if (navigator.share) {
        navigator.share({
            title: 'Hasil Prediksi Harga Bawang Merah',
            text: `Prediksi harga bawang merah {{ $result['region'] }} besok:  Rp {{ number_format($result['prediction'], 0, ',', '.') }}`,
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window. location.href);
        alert('Link hasil prediksi telah disalin ke clipboard!');
    }
}
</script>
@endsection