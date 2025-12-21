@extends('layouts.app')

@section('title', 'Dashboard - Prediksi Bawang Merah')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
@endsection

@section('content')
<div class="fade-in">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <h2><i class="bi bi-speedometer2"></i> Dashboard Prediksi Harga Bawang Merah</h2>
                    <p class="text-muted">Sistem Prediksi Komoditas Bawang Merah Untuk 9 Wilayah di Jawa Timur</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card card-stats">
                <div class="card-body">
                    <i class="bi bi-geo-alt-fill fs-1"></i>
                    <h3>{{ $sampleData['total_regions'] }}</h3>
                    <p>Wilayah Tersedia</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card card-stats">
                <div class="card-body">
                    <i class="bi bi-graph-up fs-1"></i>
                    <h3>{{ $sampleData['total_predictions'] }}</h3>
                    <p>Total Prediksi</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card card-stats">
                <div class="card-body">
                    <i class="bi bi-file-earmark-bar-graph-fill fs-1"></i>
                    <h3>9.387</h3>
                    <p>Total Dataset</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card card-stats">
                <div class="card-body">
                    <i class="bi bi-clock fs-1"></i>
                    <h3>{{ date('d/m/y', strtotime($sampleData['last_update'])) }}</h3>
                    <p>Update Terakhir</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Region Selection -->
        <div class="col-lg-2 col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5><i class="bi bi-list-ul"></i> Pilih Wilayah</h5>
                    <small class="text-muted">Klik wilayah untuk melihat trend harga</small>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        @foreach($regions as $region)
                        <button type="button" 
                                class="list-group-item list-group-item-action region-btn" 
                                data-region="{{ $region }}"
                                onclick="loadRegionData('{{ $region }}')">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-geo-alt me-2"></i>
                                    <span class="fw-bold">{{ $region }}</span>
                                </div>
                                <span class="badge bg-primary">Aktif</span>
                            </div>
                        </button>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 d-grid gap-2">
                        <a href="{{ route('prediction.form') }}" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Buat Prediksi Baru
                        </a>
                        <a href="{{ route('dashboard.comparison') }}" class="btn btn-outline-info">
                            <i class="bi bi-bar-chart"></i> Bandingkan Semua
                        </a>
                        <a href="{{ route('dashboard.uji-prediksi') }}" class="btn btn-outline-primary">
                            <i class="bi bi-table"></i> Data Uji Prediksi
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Chart Area -->
        <div class="col-lg-10 col-md-9 mb-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <div class="mb-2 mb-lg-0">
                        <h5 class="mb-1"><i class="bi bi-graph-up"></i> Trend Harga</h5>
                        <small class="text-muted"></small>
                    </div>
                    <div class="d-flex align-items-center flex-wrap">
                        <select class="form-select form-select-sm me-2 mb-2 mb-lg-0" id="yearFilter" onchange="changeFilter()" style="width: auto;">
                            <option value="2024" selected>2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                            <option value="2021">2021</option>
                            <option value="all">Semua Tahun</option>
                        </select>
                        <select class="form-select form-select-sm me-2 mb-2 mb-lg-0" id="monthFilter" onchange="changeFilter()" style="width: auto;">
                            <option value="all" selected>Semua Bulan</option>
                            <option value="1">Januari</option>
                            <option value="2">Februari</option>
                            <option value="3">Maret</option>
                            <option value="4">April</option>
                            <option value="5">Mei</option>
                            <option value="6">Juni</option>
                            <option value="7">Juli</option>
                            <option value="8">Agustus</option>
                            <option value="9">September</option>
                            <option value="10">Oktober</option>
                            <option value="11">November</option>
                            <option value="12">Desember</option>
                        </select>
                        <span id="selected-region" class="badge bg-secondary">Pilih wilayah</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="priceChart"></canvas>
                        <div class="chart-loading" id="chartLoading" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading... </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Prediction Result Panel -->
    <div id="prediction-panel" style="display: none;">
        <div style="display: flex; gap: 1.5rem; flex-wrap: wrap;">
        <!-- Prediksi Harga Besok -->
        <div style="flex: 1; min-width: 300px;">
            <div class="card border-success h-100">
                <div class="card-header bg-success text-white">
                    <h5><i class="bi bi-bullseye"></i> Prediksi Harga Besok</h5>
                </div>
                <div class="card-body prediction-result text-center">
                    <h1 id="prediction-price" class="text-success">-</h1>
                    <p class="mb-0">untuk wilayah <span id="prediction-region" class="fw-bold">-</span></p>
                    <small class="text-muted" id="prediction-date">{{ now()->addDay()->locale('id')->isoFormat('DD MMMM Y') }}</small>
                </div>
            </div>
        </div>
        <!-- Informasi Prediksi -->
        <div style="flex: 1; min-width: 300px;">
            <div class="card h-100">
                <div class="card-header">
                    <h6><i class="bi bi-info-circle"></i> Informasi Prediksi</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-4 model-stat">
                            <h6>Trend</h6>
                            <div id="trend-indicator" class="trend-stable">
                                <i class="bi bi-dash-circle fs-2"></i>
                            </div>
                        </div>
                        <div class="col-4 model-stat">
                            <h6>Confidence</h6>
                            <strong id="confidence-score">-</strong>
                        </div>
                        <div class="col-4 model-stat">
                            <h6>Window</h6>
                            <strong>7 hari</strong>
                        </div>
                    </div>
                    <div class="model-info mt-3">
                        <div class="row text-center">
                            <div class="col-6">
                                <small class="text-muted">Model Training: </small>
                                <div class="fw-bold">April 2024</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Response Time:</small>
                                <div class="fw-bold" id="response-time">-</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </div>
    </div>
@endsection

@section('scripts')
<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection