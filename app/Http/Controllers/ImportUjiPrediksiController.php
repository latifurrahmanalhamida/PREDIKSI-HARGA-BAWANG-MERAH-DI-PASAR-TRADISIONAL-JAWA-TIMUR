<?php

namespace App\Http\Controllers;

use App\Imports\DataUjiPrediksiImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ImportUjiPrediksiController extends Controller
{
    public function import()
    {
        // 9 regions sesuai dengan file yang ada
        $regions = [
            'Kediri',
            'Surabaya',
            'Malang',
            'Probolinggo',
            'Madiun',
            'Banyuwangi',
            'Blitar',
            'Jember',
            'Sumenep'
        ];

        $folder = storage_path('app/data uji/');
        $results = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($regions as $region) {
            $file = $folder . $region . '_Backtest_2024_W7.xlsx';
            
            if (file_exists($file)) {
                try {
                    Excel::import(new DataUjiPrediksiImport(strtolower($region)), $file);
                    $results[] = "✅ Import data uji <strong>$region</strong> sukses";
                    $successCount++;
                } catch (\Exception $e) {
                    $results[] = "❌ Import data uji <strong>$region</strong> gagal: " . $e->getMessage();
                    $failedCount++;
                }
            } else {
                $results[] = "⚠️ File <strong>$region</strong> tidak ditemukan: $file";
                $failedCount++;
            }
        }

        $summary = "<h2>Import Data Uji Prediksi Selesai!</h2>";
        $summary .= "<p>Berhasil: <strong>$successCount</strong> | Gagal: <strong>$failedCount</strong></p>";
        $summary .= "<ul>";
        foreach ($results as $result) {
            $summary .= "<li>$result</li>";
        }
        $summary .= "</ul>";
        $summary .= "<p><a href='/dashboard'>Kembali ke Dashboard</a></p>";

        return $summary;
    }
}
