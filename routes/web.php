<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PredictionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ImportUjiPrediksiController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});


// Dashboard Routes
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/region/{region}', [DashboardController::class, 'getRegionData'])->name('dashboard.region');
Route::get('/dashboard/comparison', [DashboardController::class, 'compareAllRegions'])->name('dashboard.comparison');
Route::get('/dashboard/trend-data', [DashboardController::class, 'getTrendData'])->name('dashboard.trend');
Route::get('/dashboard/uji-prediksi', [DashboardController::class, 'showUjiPrediksiView'])->name('dashboard.uji-prediksi');
Route::get('/uji-prediksi', [DashboardController::class, 'getUjiPrediksi']);

// Prediction Routes
Route::get('/prediction', [PredictionController::class, 'index'])->name('prediction.form');
Route::post('/prediction', [PredictionController::class, 'predict'])->name('prediction.predict');
// AJAX: Upload/parse Excel harga prediksi
Route::post('/prediction/upload-excel', [\App\Http\Controllers\PredictionExcelController::class, 'parse'])->name('prediction.upload_excel');

// Import Routes
Route::get('/import', [ImportController::class, 'showForm'])->name('import.form');
Route::post('/import', [ImportController::class, 'importHarga'])->name('import.upload');
Route::post('/import/all', [ImportController::class, 'importAll'])->name('import.all');

// Import Data Uji Prediksi (uncomment only when needed for import)
Route::get('/import-uji-prediksi', [ImportUjiPrediksiController::class, 'import']);




