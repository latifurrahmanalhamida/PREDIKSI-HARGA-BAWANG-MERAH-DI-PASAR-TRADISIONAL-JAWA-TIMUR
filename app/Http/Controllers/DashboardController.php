<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FastApiService;
use App\Models\PredictionLog;
use Carbon\Carbon;

class DashboardController extends Controller
{
    private FastApiService $fastApiService;

    public function __construct(FastApiService $fastApiService)
    {
        $this->fastApiService = $fastApiService;
    }

    public function index()
    {
        $regions = $this->fastApiService->getAvailableRegions();
        $realStats = $this->getRealStats();
        
        return view('dashboard.index', [
            'regions' => $regions,
            'sampleData' => $realStats
        ]);
    }

    // ADD THIS NEW METHOD
    public function compareAllRegions()
    {
        $regions = $this->fastApiService->getAvailableRegions();
        $comparisons = [];
        
        foreach($regions as $region) {
            // Get real data from prediction logs
            $recentPredictions = PredictionLog::forRegion($region)
                ->recent(30)
                ->get();
                
            $avgPrice = $recentPredictions->avg('predicted_price') ?: rand(40000, 60000);
            $latestPrediction = $recentPredictions->first();
            
            $prediction = $latestPrediction ? 
                $latestPrediction->predicted_price : 
                $avgPrice + rand(-5000, 5000);
                
            $confidence = $this->getRegionAccuracy($region) ?: rand(85, 95);
            
            // Determine trend
            $trend = 'stable';
            if ($prediction > $avgPrice * 1.02) {
                $trend = 'up';
            } elseif ($prediction < $avgPrice * 0.98) {
                $trend = 'down';
            }
            
            $comparisons[] = [
                'region' => $region,
                'avg_price' => round($avgPrice),
                'prediction' => round($prediction),
                'trend' => $trend,
                'confidence' => round($confidence),
                'total_predictions' => PredictionLog::forRegion($region)->count(),
                'last_updated' => $latestPrediction ? 
                    $latestPrediction->created_at->diffForHumans() : 
                    'Belum ada prediksi'
            ];
        }
        
        // Calculate statistics
        $statistics = [
            'trending_up' => collect($comparisons)->where('trend', 'up')->count(),
            'trending_down' => collect($comparisons)->where('trend', 'down')->count(),
            'avg_prediction' => round(collect($comparisons)->avg('prediction')),
            'avg_confidence' => round(collect($comparisons)->avg('confidence')),
        ];
        
        return view('dashboard.comparison', compact('comparisons', 'statistics'));
    }

    private function getRealStats()
    {
        // Real statistics from database
        $totalPredictions = PredictionLog::count();
        $thisMonthPredictions = PredictionLog::thisMonth()->count();
        
        // Calculate average accuracy (MAPE)
        $avgMape = PredictionLog::whereNotNull('mape_score')
            ->avg('mape_score') ?? 0;
        $avgAccuracy = max(0, 100 - ($avgMape * 100));
        
        // Average runtime
        $avgRuntime = PredictionLog::avg('runtime_ms') ?? 0;
        
        // Recent activity
        $recentPredictions = PredictionLog::recent(7)->count();
        
        return [
            'total_regions' => count($this->fastApiService->getAvailableRegions()),
            'total_predictions' => $totalPredictions,
            'avg_accuracy' => round($avgAccuracy, 2),
            'last_update' => now()->format('d M Y H:i'),
            'this_month_predictions' => $thisMonthPredictions,
            'recent_predictions' => $recentPredictions,
            'avg_runtime' => round($avgRuntime, 0),
            'popular_regions' => $this->getPopularRegions()
        ];
    }

    private function getPopularRegions()
    {
        return PredictionLog::selectRaw('region, COUNT(*) as prediction_count')
            ->groupBy('region')
            ->orderBy('prediction_count', 'desc')
            ->take(3)
            ->get()
            ->pluck('region')
            ->toArray();
    }

    public function getRegionData(Request $request, $region)
    {
        // Get real historical predictions for this region
        $historicalPredictions = PredictionLog::forRegion($region)
            ->recent(30)
            ->orderBy('created_at', 'desc')
            ->take(7)
            ->get()
            ->reverse()
            ->values();

        $data = [];
        $basePrice = rand(40000, 60000);
        
        if ($historicalPredictions->count() >= 3) {
            // Use real prediction data
            foreach ($historicalPredictions as $prediction) {
                $data[] = [
                    'date' => $prediction->created_at->format('Y-m-d'),
                    'price' => (int) $prediction->predicted_price
                ];
            }
        } else {
            // Fallback to generated data for demo
            for ($i = 6; $i >= 0; $i--) {
                $variation = rand(-5000, 5000);
                $price = max(25000, $basePrice + $variation);
                $data[] = [
                    'date' => now()->subDays($i)->format('Y-m-d'),
                    'price' => $price
                ];
                $basePrice = $price;
            }
        }
        
        return response()->json([
            'region' => $region,
            'historical_data' => $data,
            'prediction' => $this->generatePrediction($region, array_column($data, 'price')),
            'total_predictions' => PredictionLog::forRegion($region)->count(),
            'avg_accuracy' => $this->getRegionAccuracy($region)
        ]);
    }

    private function getRegionAccuracy($region)
    {
        $avgMape = PredictionLog:: forRegion($region)
            ->whereNotNull('mape_score')
            ->avg('mape_score') ?? 0;
            
        return round(max(0, 100 - ($avgMape * 100)), 2);
    }

    private function generatePrediction($region, $prices)
    {
        $recentPrices = array_slice($prices, -7);
        $avgPrice = array_sum($recentPrices) / count($recentPrices);
        $trend = ($recentPrices[6] - $recentPrices[0]) / 6;
        
        return [
            'price' => round($avgPrice + $trend),
            'trend' => $trend > 500 ? 'up' : ($trend < -500 ? 'down' : 'stable'),
            'confidence' => $this->getRegionAccuracy($region)
        ];
    }

    public function getTrendData(Request $request)
    {
        $region = strtolower($request->get('region', 'probolinggo'));
        $year = $request->get('year', '2024');
        $month = $request->get('month', 'all');

        $query = \App\Models\HargaHarian::where('region', $region)
            ->orderBy('tanggal', 'asc');

        // Filter by year
        if ($year !== 'all') {
            $query->whereYear('tanggal', $year);
        }

        // Filter by month
        if ($month !== 'all') {
            $query->whereMonth('tanggal', $month);
        }

        $items = $query->get(['tanggal', 'harga']);

        return response()->json([
            'labels' => $items->pluck('tanggal')->map(fn($tgl) => \Carbon\Carbon::parse($tgl)->format('d M Y')),
            'values' => $items->pluck('harga')->values(),
        ]);
    }


    public function getUjiPrediksi(Request $request)
    {
        $region = strtolower($request->get('region', 'surabaya'));
        $month = $request->get('month', 'all');
        
        $query = \App\Models\DataUjiPrediksi::where('region', $region)
            ->orderBy('tanggal', 'asc');
        
        // Filter by month if specified (all years)
        if ($month !== 'all') {
            $query->whereMonth('tanggal', $month);
        }
        
        $data = $query->get();

        $avg_mape = $data->avg('error'); // Rata2 error (%) untuk ringkasan

        return response()->json([
            'list' => $data,
            'avg_mape' => round($avg_mape, 2),
            'count' => $data->count(),
            'region' => ucfirst($region)
        ]);
    }

    public function showUjiPrediksiView()
    {
        return view('dashboard.uji-prediksi');
    }
}
