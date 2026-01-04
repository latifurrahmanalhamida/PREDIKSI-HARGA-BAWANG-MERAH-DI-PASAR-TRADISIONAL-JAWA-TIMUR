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
        <div class="col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="card card-stats">
                <div class="card-body">
                    <i class="bi bi-geo-alt-fill fs-1"></i>
                    <h3>{{ $sampleData['total_regions'] }}</h3>
                    <p>Wilayah Tersedia</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 col-sm-6 mb-3">
            <div class="card card-stats">
                <div class="card-body">
                    <i class="bi bi-graph-up fs-1"></i>
                    <h3>{{ $sampleData['total_predictions'] }}</h3>
                    <p>Total Prediksi</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-12 col-sm-12 mb-3">
            <div class="card card-stats">
                <div class="card-body">
                    <i class="bi bi-file-earmark-bar-graph-fill fs-1"></i>
                    <h3>9.387</h3>
                    <p>Total Dataset</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <!-- Region Selection -->
        <div class="col-lg-2 col-md-3 col-12 mb-4">
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
                            <i class="bi bi-geo-alt me-2"></i>
                            <span class="fw-bold">{{ $region }}</span>
                        </button>
                        @endforeach
                    </div>
                    
                    <div class="mt-4 d-grid gap-2">
                        <a href="{{ route('prediction.form') }}" class="btn btn-success">
                            <i class="bi bi-plus-circle"></i> Buat Prediksi Baru
                        </a>
                        <a href="{{ route('dashboard.uji-prediksi') }}" class="btn btn-outline-teal">
                            <i class="bi bi-table"></i> Data Uji
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
@endsection

@section('scripts')
<script src="{{ asset('js/dashboard.js') }}"></script>
@endsection