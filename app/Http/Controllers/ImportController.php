<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\HargaHarianImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function showForm()
    {
        return view('import.form');
    }

    public function importHarga(Request $request)
    {
        $request->validate([
            'region' => 'required|string',
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            $region = $request->input('region');
            $file = $request->file('file');

            // Import the file
            Excel::import(new HargaHarianImport($region), $file);

            return redirect()->back()->with('success', "Data harga untuk region {$region} berhasil diimport!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    public function importAll()
    {
        $regions = [
            'Probolinggo' => 'Data_Clean_Probolinggo.xlsx',
            'Malang' => 'Data_Clean_Malang.xlsx',
            'Kediri' => 'Data_Clean_Kediri.xlsx',
            'Banyuwangi' => 'Data_Clean_Banyuwangi.xlsx',
            'Surabaya' => 'Data_Clean_Surabaya.xlsx',
            'Blitar' => 'Data_Clean_Blitar.xlsx',
            'Jember' => 'Data_Clean_Jember.xlsx',
            'Madiun' => 'Data_Clean_Madiun.xlsx',
            'Sumenep' => 'Data_Clean_Sumenep.xlsx'
        ];

        $imported = 0;
        $failed = [];

        foreach ($regions as $region => $filename) {
            $filePath = storage_path('app/data clean/' . $filename);
            
            if (file_exists($filePath)) {
                try {
                    Excel::import(new HargaHarianImport(strtolower($region)), $filePath);
                    $imported++;
                } catch (\Exception $e) {
                    $failed[] = "{$region}: {$e->getMessage()}";
                }
            } else {
                $failed[] = "{$region}: File tidak ditemukan";
            }
        }

        $message = "Import selesai! {$imported} region berhasil diimport.";
        if (count($failed) > 0) {
            $message .= "\n\nGagal: " . implode(", ", $failed);
        }

        return back()->with('success', $message);
    }
}
