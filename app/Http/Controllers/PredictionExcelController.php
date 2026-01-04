<?php

namespace App\Http\Controllers;

use App\Http\Requests\PredictionExcelRequest;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class PredictionExcelController extends Controller
{
    public function parse(PredictionExcelRequest $request)
    {
        $file = $request->file('harga_excel');
        $rows = [];
        try {
            $ext = strtolower($file->getClientOriginalExtension());
            if ($ext === 'csv') {
                $csv = array_map('str_getcsv', file($file->getRealPath()));
                foreach ($csv as $row) {
                    if (isset($row[0]) && is_numeric($row[0])) {
                        $rows[] = (int) $row[0];
                    }
                }
            } else {
                $data = Excel::toArray([], $file);
                $sheet = $data[0];
                foreach ($sheet as $row) {
                    if (isset($row[0]) && is_numeric($row[0])) {
                        $rows[] = (int) $row[0];
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Excel parse error: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => 'Gagal membaca file Excel. Pastikan format benar.']);
        }
        if (count($rows) !== 7) {
            return response()->json(['success' => false, 'error' => 'File harus berisi 7 baris harga.']);
        }
        return response()->json(['success' => true, 'harga' => $rows]);
    }
}
