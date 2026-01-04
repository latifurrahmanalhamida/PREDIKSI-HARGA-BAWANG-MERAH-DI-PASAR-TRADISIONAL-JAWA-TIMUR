<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Imports\DataUjiPrediksiImport;
use Maatwebsite\Excel\Facades\Excel;

$regions = [
    'kediri', 'surabaya', 'malang', 'probolinggo', 
    'madiun', 'banyuwangi', 'blitar', 'jember', 'sumenep'
];

echo "Starting import...\n";
$successCount = 0;
$failedCount = 0;

foreach ($regions as $region) {
    $file = storage_path('app/data uji/' . ucfirst($region) . '_Backtest_2024_W7.xlsx');
    
    if (file_exists($file)) {
        try {
            Excel::import(new DataUjiPrediksiImport($region), $file);
            echo "✅ Imported: " . ucfirst($region) . "\n";
            $successCount++;
        } catch (Exception $e) {
            echo "❌ Failed: " . ucfirst($region) . " - " . $e->getMessage() . "\n";
            $failedCount++;
        }
    } else {
        echo "⚠️  File not found: " . ucfirst($region) . "\n";
        $failedCount++;
    }
}

echo "\n=== SUMMARY ===\n";
echo "Success: $successCount\n";
echo "Failed: $failedCount\n";

// Check total records
$total = \App\Models\DataUjiPrediksi::count();
$minDate = \App\Models\DataUjiPrediksi::min('tanggal');
$maxDate = \App\Models\DataUjiPrediksi::max('tanggal');

echo "\nTotal records in DB: $total\n";
echo "Date range: $minDate to $maxDate\n";
