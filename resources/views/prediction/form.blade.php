@extends('layouts.app')

@section('title', 'Buat Prediksi - Prediksi Bawang Merah')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/prediction.css') }}">
@endsection

@section('content')
<div class="prediction-form fade-in">
    <div class="row justify-content-center">
        <div class="col-12">
            <!-- Header -->
            <div class="card mb-4">
                <div class="card-body text-center">
                    <h3><i class="bi bi-calculator"></i> Buat Prediksi Harga Bawang Merah</h3>
                    <p class="text-muted">Masukkan data harga 7 hari terakhir untuk mendapatkan prediksi harga besok</p>
                </div>
            </div>

            <!-- Form -->
            <div class="card">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Terjadi kesalahan:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('prediction.predict') }}" method="POST" id="predictionForm">
                        @csrf
                        
                        <!-- Region Selection -->
                        <div class="mb-4">
                            <label for="region" class="form-label">
                                <i class="bi bi-geo-alt"></i> Pilih Wilayah: 
                            </label>
                            <select name="region" id="region" class="form-select form-select-lg" required>
                                <option value="">-- Pilih Wilayah --</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region }}" {{ old('region') == $region ? 'selected' : '' }}>
                                        {{ $region }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Price Input Section -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="bi bi-graph-up"></i> Masukkan Harga 7 Hari Terakhir (Rupiah):
                            </label>
                            <p class="text-muted small">
                                <i class="bi bi-info-circle"></i> 
                                Masukkan harga dari hari ke-1 (paling lama) hingga hari ke-7 (hari kemarin)
                            </p>
                            
                            <div class="row">
                                @for ($i = 1; $i <= 7; $i++)
                                    <div class="col-lg-4 col-md-6 mb-3">
                                        <label for="day{{ $i }}" class="form-label">
                                            <strong>Hari ke-{{ $i }}:</strong>
                                            @if($i == 1) 
                                                <small class="text-muted">(7 hari lalu)</small>
                                            @elseif($i == 7) 
                                                <small class="text-muted">(kemarin)</small>
                                            @endif
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" 
                                                   name="day{{ $i }}" 
                                                   id="day{{ $i }}" 
                                                   class="form-control price-input" 
                                                   value=""
                                                   placeholder="Masukkan harga" 
                                                   min="10000"
                                                   max="200000"
                                                   required>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>

                        <!-- Quick Fill Section -->
                        <div class="quick-fill-section">
                            <label class="form-label">
                                <i class="bi bi-lightning"></i> Quick Fill (Contoh Data):
                            </label>
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="fillSampleData('trend_up')">
                                    <i class="bi bi-arrow-up"></i> Trend Naik
                                </button>
                                <button type="button" class="btn btn-outline-danger btn-sm" onclick="fillSampleData('trend_down')">
                                    <i class="bi bi-arrow-down"></i> Trend Turun
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="fillSampleData('stable')">
                                    <i class="bi bi-dash"></i> Harga Stabil
                                </button>
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshChartPreview()">
                                    <i class="bi bi-arrow-clockwise"></i> Lihat Grafik
                                </button>
                                <button type="button" class="btn btn-outline-warning btn-sm" onclick="clearForm()">
                                    <i class="bi bi-trash"></i> Clear
                                </button>
                            </div>
                        </div>

                        <!-- Chart Preview -->
                        <div class="mb-4 preview-chart" id="previewChart" style="display: none;">
                            <label class="form-label">
                                <i class="bi bi-graph-up"></i> Preview Data Input:
                            </label>
                            <canvas id="inputChart" width="400" height="150"></canvas>
                        </div>

                        <!-- Submit Section -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="bi bi-cpu"></i> Prediksi Harga Besok
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('js/prediction_bisa.js') }}?v=99"></script>
@endsection