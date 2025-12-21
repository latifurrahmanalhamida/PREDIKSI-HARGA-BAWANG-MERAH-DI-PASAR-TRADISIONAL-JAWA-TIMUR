<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FastApiService;
use App\Models\PredictionLog;
use Carbon\Carbon;

class PredictionController extends Controller
{
    private FastApiService $fastApiService;

    public function __construct(FastApiService $fastApiService)
    {
        $this->fastApiService = $fastApiService;
    }

    public function index()
    {
        $regions = $this->fastApiService->getAvailableRegions();
        return view('prediction.form', compact('regions'));
    }

    public function predict(Request $request)
    {
        // Validation
        $validated = $request->validate([
            'region' => 'required|string',
            'day1' => 'required|numeric|min:10000|max:200000',
            'day2' => 'required|numeric|min:10000|max:200000',
            'day3' => 'required|numeric|min: 10000|max:200000',
            'day4' => 'required|numeric|min:10000|max:200000',
            'day5' => 'required|numeric|min:10000|max:200000',
            'day6' => 'required|numeric|min:10000|max:200000',
            'day7' => 'required|numeric|min:10000|max:200000',
        ]);

        try {
            // Call FastAPI
            $startTime = microtime(true);
            $apiResponse = $this->fastApiService->predictNext($validated['region'], [
                $validated['day1'], $validated['day2'], $validated['day3'],
                $validated['day4'], $validated['day5'], $validated['day6'], $validated['day7']
            ]);
            $endTime = microtime(true);
            $runtimeMs = round(($endTime - $startTime) * 1000, 2);

            // Check if API call was successful
            if (!$apiResponse['success']) {
                throw new \Exception($apiResponse['error'] ?? 'Failed to get prediction from API');
            }

            $result = $apiResponse['data'];

            // Calculate trend
            $lastPrice = $validated['day7'];
            $prediction = $result['prediction'];
            $trendDirection = 'stable';
            $trendPercentage = 0;

            if ($prediction > $lastPrice) {
                $trendDirection = 'up';
                $trendPercentage = round((($prediction - $lastPrice) / $lastPrice) * 100, 2);
            } elseif ($prediction < $lastPrice) {
                $trendDirection = 'down';
                $trendPercentage = round((($lastPrice - $prediction) / $lastPrice) * 100, 2);
            }

            // Extract MAPE from FastAPI response
            $mapeScore = null;
            if (isset($result['meta']) && isset($result['meta']['mape_2024'])) {
                $mapeScore = $result['meta']['mape_2024'];
            } elseif (isset($result['mape'])) {
                $mapeScore = $result['mape'];
            } elseif (isset($result['model_info']) && isset($result['model_info']['mape'])) {
                $mapeScore = $result['model_info']['mape'];
            }

            // Save to database
            $predictionLog = PredictionLog::create([
                'region' => $validated['region'],
                'input_prices' => [
                    $validated['day1'], $validated['day2'], $validated['day3'],
                    $validated['day4'], $validated['day5'], $validated['day6'], $validated['day7']
                ],
                'predicted_price' => $prediction,
                'confidence_score' => $result['confidence'] ??  null,
                'runtime_ms' => $runtimeMs,
                'mape_score' => $mapeScore,
                'trend_direction' => $trendDirection,
                'trend_percentage' => $trendPercentage,
                'predicted_for_date' => Carbon::tomorrow()
            ]);

            // Enhance result for display with REAL data from API
            // runtime_ms: use Laravel measured time (more accurate for total roundtrip)
            $result['runtime_ms'] = $runtimeMs;
            
            // window_size: from API response (should be 7)
            if (!isset($result['window_size'])) {
                $result['window_size'] = 7; // fallback
            }
            
            // trend: calculated from actual input vs prediction
            $result['trend_direction'] = $trendDirection;
            $result['trend_percentage'] = $trendPercentage;
            
            // predicted date: from database record
            $result['predicted_for_date'] = $predictionLog->predicted_for_date;
            
            $result['log_id'] = $predictionLog->id;

            return view('prediction.result', [
                'result' => $result,
                'input' => $validated
            ]);

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Terjadi kesalahan saat melakukan prediksi:  ' . $e->getMessage()])
                ->withInput();
        }
    }
}