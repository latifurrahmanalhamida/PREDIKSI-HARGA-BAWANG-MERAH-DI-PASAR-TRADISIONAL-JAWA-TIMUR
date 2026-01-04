@extends('layouts.app')

@section('title', 'Data Uji - Prediksi Bawang Merah')

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
    
    /* Custom Teal Button Style */
    .btn-outline-teal {
        color: #14919b;
        border-color: #14919b;
    }
    
    .btn-outline-teal:hover {
        color: white;
        background-color: #14919b;
        border-color: #14919b;
    }
    
    .btn-outline-teal.active {
        color: white;
        background-color: #0d7377;
        border-color: #0d7377;
    }
    
    /* Mobile Responsive */
    @media (max-width: 767px) {
        .region-select-btn {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
        }
        
        .table-responsive {
            max-height: 400px;
        }
        
        #comparisonChart {
            max-height: 250px;
        }
        
        .card-body h6 {
            font-size: 0.95rem;
        }
        
        .list-group-item {
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
        }
    }
    
    @media (max-width: 576px) {
        .region-select-btn {
            padding: 0.4rem 0.6rem;
            font-size: 0.8rem;
        }
        
        h2 {
            font-size: 1.5rem;
        }
        
        .table {
            font-size: 0.85rem;
        }
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
                                            class="btn btn-outline-teal region-select-btn {{ $loop->first ? 'active' : '' }}" 
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
    <div class="row mb-3" id="chartContainer" style="display: none;">
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
<div class="row d-flex align-items-start" id="tableContainer" style="display: none;">
    
    <div class="col-12 col-md-9 col-lg-9 mb-3">
        <div class="card"> 
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="bi bi-table"></i> Hasil Prediksi Model pada Data Uji (<span id="active-region">-</span>)</h5>
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
                                <td colspan="5" class="text-center text-muted">
                                    <i class="bi bi-info-circle"></i> Pilih wilayah untuk melihat data
                                    Loading data...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">
                    <i class="bi bi-info-circle"></i> Pada Tabel Ini Merupakan DataSet Yang Diambil Dari Data uji/tes Yang di Lakukan Pada Masing-Masing Wilayah,   
                    Gunakan scroll untuk melihat semua data. Error berwarna hijau (&lt;2%), kuning (2-5%), merah (&gt;5%).
                </small>

                <!-- Interpretasi Nilai MAPE (Kompetensi Model Peramalan) -->
                <!-- <div class="card mt-4 shadow-sm border-info" style="max-width: 480px; margin: 0 auto;">
                    <div class="card-header bg-info text-white py-2 px-3">
                        <strong><i class="bi bi-bar-chart"></i> Interpretasi Nilai MAPE</strong>
                    </div>
                    <div class="card-body py-2 px-3">
                        <table class="table table-bordered table-sm mb-0" style="background: #f8fbfd;">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Range MAPE</th>
                                    <th class="text-center">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-center"><span class="badge bg-success">&lt; 10%</span></td>
                                    <td class="text-center">Sangat Baik</td>
                                </tr>
                                <tr>
                                    <td class="text-center"><span class="badge bg-primary">10% - 20%</span></td>
                                    <td class="text-center">Baik</td>
                                </tr>
                                <tr>
                                    <td class="text-center"><span class="badge bg-warning text-dark">20% - 50%</span></td>
                                    <td class="text-center">Layak</td>
                                </tr>
                                <tr>
                                    <td class="text-center"><span class="badge bg-danger">&gt; 50%</span></td>
                                    <td class="text-center">Buruk</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-3 col-lg-3 mb-3">
        <div class="card"> 
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0"><i class="bi bi-clipboard-data"></i> Ringkasan Akurasi</h6>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                        <strong class="small">Region:</strong>
                        <span id="active-region2" class="badge bg-secondary">-</span>
                    </li>
                    <li class="list-group-item p-2">
                        <strong class="small">Rentang Data Uji:</strong>
                        <div id="uji-date-range" class="text-muted small mt-1">-</div>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                        <strong class="small">Jumlah Hari:</strong>
                        <span id="uji-count" class="badge bg-info">-</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center p-2">
                        <strong class="small">MAPE rata-rata:</strong>
                        <span id="avg-mape2" class="text-success fs-6"><strong>-</strong>%</span>
                    </li>
                </ul>

                    <!-- Interpretasi Nilai MAPE (Kompetensi Model Peramalan) -->
                    <div class="card mt-4 shadow-sm border-info" style="max-width: 480px; margin: 24px auto 0 auto;">
                        <div class="card-header bg-info text-white py-2 px-3">
                            <strong><i class="bi bi-bar-chart"></i> Interpretasi Nilai MAPE</strong>
                        </div>
                        <div class="card-body py-2 px-3">
                            <table class="table table-bordered table-sm mb-0" style="background: #f8fbfd;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center">Range MAPE</th>
                                        <th class="text-center">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-center"><span class="badge bg-success">&lt; 10%</span></td>
                                        <td class="text-center">Sangat Baik</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><span class="badge bg-primary">10% - 20%</span></td>
                                        <td class="text-center">Baik</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><span class="badge bg-warning text-dark">20% - 50%</span></td>
                                        <td class="text-center">Layak</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center"><span class="badge bg-danger">&gt; 50%</span></td>
                                        <td class="text-center">Buruk</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                <div class="d-grid gap-2">
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                    <a href="{{ route('prediction.form') }}" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-circle"></i> Buat Prediksi
                    </a>
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
