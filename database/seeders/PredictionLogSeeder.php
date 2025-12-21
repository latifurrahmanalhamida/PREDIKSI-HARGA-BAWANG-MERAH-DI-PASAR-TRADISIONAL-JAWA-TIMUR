<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PredictionLog;
use Carbon\Carbon;

class PredictionLogSeeder extends Seeder
{
    public function run()
    {
        $regions = ['Kediri', 'Banyuwangi', 'Surabaya', 'Blitar', 'Jember', 'Madiun', 'Malang', 'Probolinggo', 'Sumenep'];
        
        foreach($regions as $region) {
            // Create 10-15 sample predictions per region
            for($i = 1; $i <= rand(10, 15); $i++) {
                $basePrice = $this->getRegionBasePrice($region);
                $variation = rand(-8000, 8000);
                $predictedPrice = max(25000, $basePrice + $variation);
                
                // Generate realistic input prices
                $inputPrices = [];
                $currentPrice = $predictedPrice - rand(-3000, 3000);
                for($j = 1; $j <= 7; $j++) {
                    $inputPrices[] = max(20000, $currentPrice + rand(-2000, 2000));
                    $currentPrice = $inputPrices[$j-1];
                }
                
                PredictionLog::create([
                    'region' => $region,
                    'input_prices' => $inputPrices,
                    'predicted_price' => $predictedPrice,
                    'confidence_score' => rand(8500, 9800) / 100,
                    'runtime_ms' => rand(1800, 3500),
                    'mape_score' => rand(200, 800) / 10000, // 0.02-0.08
                    'trend_direction' => ['up', 'down', 'stable'][rand(0, 2)],
                    'trend_percentage' => rand(-1500, 1500) / 100,
                    'predicted_for_date' => Carbon::now()->addDay(),
                    'created_at' => Carbon::now()->subDays(rand(1, 30)),
                    'updated_at' => Carbon::now()->subDays(rand(1, 30))
                ]);
            }
        }
    }
    
    private function getRegionBasePrice($region)
    {
        $basePrices = [
            'Kediri' => 48000,
            'Banyuwangi' => 45000,
            'Surabaya' => 58000,
            'Blitar' => 46000,
            'Jember' => 52000,
            'Madiun' => 54000,
            'Malang' => 53000,
            'Probolinggo' => 57000,
            'Sumenep' => 51000
        ];
        
        return $basePrices[$region] ?? 50000;
    }
}