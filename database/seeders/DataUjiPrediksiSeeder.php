<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DataUjiPrediksi;
use Carbon\Carbon;

class DataUjiPrediksiSeeder extends Seeder
{
    public function run()
    {
        $regions = ['Kediri', 'Banyuwangi', 'Surabaya', 'Blitar', 'Jember', 'Madiun', 'Malang', 'Probolinggo', 'Sumenep'];
        
        foreach($regions as $region) {
            // Create sample data for the last 30 days
            for($i = 0; $i < 30; $i++) {
                $basePrice = $this->getRegionBasePrice($region);
                $variation = rand(-8000, 8000);
                $hargaAktual = max(25000, $basePrice + $variation);
                
                // Generate predicted price with some error
                $errorPercentage = rand(-10, 10) / 100; // -10% to +10%
                $hargaPrediksi = $hargaAktual * (1 + $errorPercentage);
                $selisih = $hargaPrediksi - $hargaAktual;
                $error = abs($selisih / $hargaAktual);
                
                DataUjiPrediksi::create([
                    'region' => $region,
                    'tanggal' => Carbon::now()->subDays($i),
                    'harga_aktual' => round($hargaAktual, 2),
                    'harga_prediksi' => round($hargaPrediksi, 2),
                    'selisih' => round($selisih, 2),
                    'error' => round($error, 4),
                    'created_at' => Carbon::now()->subDays($i),
                    'updated_at' => Carbon::now()->subDays($i)
                ]);
            }
        }
    }
    
    private function getRegionBasePrice($region)
    {
        $basePrices = [
            'Kediri' => 48000,
            'Banyuwangi' => 46000,
            'Surabaya' => 50000,
            'Blitar' => 45000,
            'Jember' => 47000,
            'Madiun' => 44000,
            'Malang' => 49000,
            'Probolinggo' => 46500,
            'Sumenep' => 43000
        ];
        
        return $basePrices[$region] ?? 45000;
    }
}
