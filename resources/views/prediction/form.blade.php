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

                    <form action="{{ route('prediction.predict') }}" method="POST" id="predictionForm" enctype="multipart/form-data">
                        @csrf

                        <!-- Region Selection -->
                        <div class="mb-4">
                            <label for="region" class="form-label">
                                <i class="bi bi-geo-alt"></i> Pilih Wilayah: 
                            </label>
                            <select name="region" id="region" class="form-select form-select-lg">
                                <option value="">-- Pilih Wilayah --</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region }}" {{ old('region') == $region ? 'selected' : '' }}>
                                        {{ $region }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Input Mode Tabs -->
                        <div class="input-mode-tabs mb-4">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="button" class="btn-input-mode active" id="manual-tab" data-target="manualInput">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>Input Manual</span>
                                    <small>Ketik harga satu per satu</small>
                                </button>
                                <button type="button" class="btn-input-mode" id="excel-tab" data-target="excelInput">
                                    <i class="bi bi-file-earmark-excel"></i>
                                    <span>Upload File</span>
                                    <small>Import dari Excel/CSV</small>
                                </button>
                            </div>
                        </div>
                        <div class="tab-content" id="inputModeTabsContent">
                            <!-- Manual Input Tab -->
                            <div class="tab-pane fade show active" id="manualInput" role="tabpanel" aria-labelledby="manual-tab">
                                <label class="form-label">
                                    <i class="bi bi-graph-up"></i> Masukkan Harga 7 Hari Terakhir (Rupiah).
                                </label>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle-fill"></i> 
                                    <strong>Ketentuan Input Harga:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><strong>Minimal:</strong> Rp 10.000</li>
                                        <li><strong>Maksimal:</strong> Rp 200.000</li>
                                        <li>Masukkan data harga bawang merah selama 7 hari terakhir</li>
                                    </ul>
                                </div>
                                <div class="row">
                                    @for ($i = 1; $i <= 7; $i++)
                                        <div class="col-lg-6 col-md-6 mb-3">
                                            <label for="day{{ $i }}" class="form-label">
                                                <strong>Hari ke-{{ $i }}:</strong>
                                                @if($i == 1) 
                                                    <small class="text-muted">(7 hari lalu)</small>
                                                @elseif($i == 7) 
                                                    <small class="text-muted">(kemarin)</small>
                                                @endif
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" 
                                                       name="day{{ $i }}" 
                                                       id="day{{ $i }}" 
                                                       class="form-control price-input" 
                                                       value=""
                                                       placeholder="Masukkan harga" 
                                                       min="10000"
                                                       max="200000">
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                            <!-- Excel Upload Tab -->
                            <div class="tab-pane fade" id="excelInput" role="tabpanel" aria-labelledby="excel-tab">
                                <div class="upload-zone">
                                    <div class="upload-icon">
                                        <i class="bi bi-cloud-arrow-up"></i>
                                    </div>
                                    <h5>Upload File Excel</h5>
                                    <p class="text-muted mb-3">Format yang didukung: .xlsx, .xls, .csv</p>
                                    <label for="harga_excel" class="btn btn-outline-primary btn-lg">
                                        <i class="bi bi-folder2-open"></i> Pilih File
                                    </label>
                                    <input type="file" name="harga_excel" id="harga_excel" class="d-none" accept=".xlsx,.xls,.csv">
                                    <div id="selectedFileName" class="mt-2 text-muted"></div>
                                </div>
                                <div id="excelPreview" class="mt-3" style="display:none;">
                                    <label class="form-label"><i class="bi bi-eye"></i> Preview Harga dari Excel:</label>
                                    <div id="excelPreviewTable"></div>
                                </div>
                            </div>
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

<!-- Custom Alert Modal -->
<div id="customAlertModal" class="custom-alert-overlay" style="display: none;">
    <div class="custom-alert-box">
        <div class="custom-alert-icon" id="alertIcon">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div class="custom-alert-title" id="alertTitle">PERHATIAN!</div>
        <div class="custom-alert-message" id="alertMessage"></div>
        <div class="custom-alert-hint">Klik di mana saja untuk menutup</div>
    </div>
</div>

<style>
.custom-alert-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    z-index: 9999;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding-top: 80px;
    animation: fadeIn 0.2s ease;
}

.custom-alert-box {
    background: white;
    border-radius: 12px;
    padding: 20px 25px;
    max-width: 380px;
    width: 90%;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.25);
    text-align: center;
    animation: popIn 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    border-left: 4px solid #14919b;
    position: relative;
}

.custom-alert-icon {
    font-size: 42px;
    margin-bottom: 12px;
    color: #14919b;
    animation: scaleIn 0.3s ease;
}

.custom-alert-icon.warning {
    color: #f39c12;
}

.custom-alert-icon.error {
    color: #dc3545;
}

.custom-alert-icon.info {
    color: #17a2b8;
}

.custom-alert-title {
    font-size: 18px;
    font-weight: bold;
    color: #2c3e50;
    margin-bottom: 10px;
}

.custom-alert-message {
    font-size: 14px;
    color: #555;
    line-height: 1.6;
    margin-bottom: 12px;
    white-space: pre-line;
}

.custom-alert-hint {
    font-size: 11px;
    color: #999;
    font-style: italic;
    padding: 6px 10px;
    background: #f8f9fa;
    border-radius: 6px;
    margin-top: 8px;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes popIn {
    0% {
        transform: scale(0.7) translateY(-30px);
        opacity: 0;
    }
    100% {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
}

@keyframes scaleIn {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

.custom-alert-overlay:hover {
    cursor: pointer;
}

@media (max-width: 576px) {
    .custom-alert-box {
        max-width: 320px;
        padding: 18px 20px;
    }
    
    .custom-alert-icon {
        font-size: 36px;
    }
    
    .custom-alert-title {
        font-size: 16px;
    }
    
    .custom-alert-message {
        font-size: 13px;
    }
}
</style>

@endsection

@section('scripts')
<!-- SheetJS for Excel generation -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>
<script src="{{ asset('js/prediction_bisa.js') }}?v=104"></script>
@endsection