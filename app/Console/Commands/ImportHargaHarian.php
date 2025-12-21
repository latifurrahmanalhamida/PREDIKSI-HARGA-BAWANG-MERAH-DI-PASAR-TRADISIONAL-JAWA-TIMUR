<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\HargaHarianImport;

class ImportHargaHarian extends Command
{
    protected $signature = 'import:harga-harian';
    protected $description = 'Import semua data harga harian dari Excel';

    public function handle()
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

        $this->info('Memulai import data harga harian...');

        $imported = 0;
        $failed = [];

        foreach ($regions as $region => $filename) {
            $filePath = storage_path('app/data clean/' . $filename);
            
            if (file_exists($filePath)) {
                try {
                    $this->info("Importing {$region}...");
                    Excel::import(new HargaHarianImport(strtolower($region)), $filePath);
                    $imported++;
                    $this->info("✓ {$region} berhasil diimport");
                } catch (\Exception $e) {
                    $failed[] = $region;
                    $this->error("✗ {$region} gagal: " . $e->getMessage());
                }
            } else {
                $failed[] = $region;
                $this->error("✗ {$region}: File tidak ditemukan di {$filePath}");
            }
        }

        $this->info("\n" . str_repeat('=', 50));
        $this->info("Import selesai!");
        $this->info("Berhasil: {$imported} region");
        $this->info("Gagal: " . count($failed) . " region");
        
        if (count($failed) > 0) {
            $this->error("Region yang gagal: " . implode(', ', $failed));
        }

        // Show summary
        $total = \DB::table('harga_harian')->count();
        $this->info("\nTotal data di database: {$total} records");

        return 0;
    }
}
