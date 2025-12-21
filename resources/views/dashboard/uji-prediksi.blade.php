@extends('layouts.app')

@section('title', 'Data Uji Prediksi - Prediksi Bawang Merah')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
<style>
    .table-responsive {
        max-height: 600px;
        overflow-y: auto;
    }
    #ujiPrediksiTable thead {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
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
                    <h2><i class="bi bi-bar-chart-line"></i> HASIL PREDIKSI HARGA PADA DATA UJI</h2>
                    <p class="text-muted">Hasil prediksi model LSTM pada data uji dari 15 maret 2024 sampai 31 desember 2024 </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Region Selector & Month Filter -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8 mb-3 mb-md-0">
                            <label class="form-label mb-2"><strong>Pilih Wilayah:</strong></label>
                            <div class="btn-group" role="group">
                                @foreach(['Kediri', 'Surabaya', 'Malang', 'Probolinggo', 'Madiun', 'Banyuwangi', 'Blitar', 'Jember', 'Sumenep'] as $region)
                                    <button type="button" 
                                            class="btn btn-outline-primary region-select-btn {{ $loop->first ? 'active' : '' }}" 
                                            onclick="selectRegion('{{ strtolower($region) }}')">
                                        {{ $region }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label mb-2"><strong>Filter Periode:</strong></label>
                            <div class="input-group">
                                <select class="form-select" id="monthFilter" onchange="applyFilter()">
                                    <option value="all">Semua Data (Mar - Des 2024)</option>
                                    <option value="3">Maret 2024</option>
                                    <option value="4">April 2024</option>
                                    <option value="5">Mei 2024</option>
                                    <option value="6">Juni 2024</option>
                                    <option value="7">Juli 2024</option>
                                    <option value="8">Agustus 2024</option>
                                    <option value="9">September 2024</option>
                                    <option value="10">Oktober 2024</option>
                                    <option value="11">November 2024</option>
                                    <option value="12">Desember 2024</option>
                                </select>
                                <button class="btn btn-outline-secondary" onclick="resetFilter()" title="Reset Filter">
                                    <i class="bi bi-arrow-clockwise"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Visualization -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h5><i class="bi bi-graph-up"></i> Grafik Perbandingan Harga Aktual vs Prediksi Model</h5>
                </div>
                <div class="card-body">
                    <canvas id="comparisonChart" height="80"></canvas>
                    <div id="chartLoading" style="display: none; text-align: center; padding: 100px 0;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Memuat grafik...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Data Uji Prediksi -->
    <div class="row">
        <div class="col-lg-8 mb-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="bi bi-table"></i> Hasil Prediksi Model pada Data Uji (<span id="active-region">Surabaya</span>)</h5>
                    <span class="badge bg-info fs-6">MAPE Rata-rata: <span id="avg-mape">-</span>%</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm" id="ujiPrediksiTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Harga Real</th>
                                    <th>Prediksi Model</th>
                                    <th>Selisih</th>
                                    <th>Error (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        Loading data...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> Prediksi di bawah diambil dari data uji/test pada periode validasi. 
                        Gunakan scroll untuk melihat semua data. Error berwarna hijau (&lt;2%), kuning (2-5%), merah (&gt;5%).
                    </small>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-3">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5><i class="bi bi-clipboard-data"></i> Ringkasan Akurasi Model</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Region:</strong>
                            <span id="active-region2" class="badge bg-secondary">-</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Rentang Data Uji:</strong>
                            <span id="uji-date-range" class="text-muted small">-</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>Jumlah Hari Uji:</strong>
                            <span id="uji-count" class="badge bg-info">-</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <strong>MAPE rata-rata:</strong>
                            <span id="avg-mape2" class="text-success fs-5"><strong>-</strong>%</span>
                        </li>
                    </ul>

                    <div class="alert alert-light mt-3">
                        <small>
                            <strong>Interpretasi MAPE:</strong><br>
                            • &lt; 2% = Excellent<br>
                            • 2-5% = Good<br>
                            • 5-10% = Acceptable<br>
                            • &gt; 10% = Poor
                        </small>
                    </div>

                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                        </a>
                        <a href="{{ route('prediction.form') }}" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Buat Prediksi Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/uji-prediksi.js') }}"></script>
<script>
    function selectRegion(region) {
        // Update active button
        document.querySelectorAll('.region-select-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
        
        // Update region display
        document.getElementById('active-region').textContent = region.charAt(0).toUpperCase() + region.slice(1);
        document.getElementById('active-region2').textContent = region.charAt(0).toUpperCase() + region.slice(1);
        
        // Load data for selected region with current month filter
        const month = document.getElementById('monthFilter').value;
        loadUjiPrediksi(region, month);
    }
</script>
@endsection
