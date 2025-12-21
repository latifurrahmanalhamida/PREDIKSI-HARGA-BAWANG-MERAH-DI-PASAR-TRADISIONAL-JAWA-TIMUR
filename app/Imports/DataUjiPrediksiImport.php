<?php

namespace App\Imports;

use App\Models\DataUjiPrediksi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class DataUjiPrediksiImport implements ToModel, WithHeadingRow
{
    protected $region;

    public function __construct($region)
    {
        $this->region = $region;
    }

    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['tanggal']) || empty($row['actual_real'])) {
            return null;
        }

        // Parse tanggal (handle various date formats)
        $tanggal = null;
        if (is_numeric($row['tanggal'])) {
            // Excel serial date
            $tanggal = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tanggal']));
        } elseif ($row['tanggal'] instanceof \DateTime) {
            // Already DateTime object
            $tanggal = Carbon::instance($row['tanggal']);
        } else {
            // String date
            $tanggal = Carbon::parse($row['tanggal']);
        }

        return new DataUjiPrediksi([
            'region' => $this->region,
            'tanggal' => $tanggal,
            'harga_aktual' => (float) $row['actual_real'],
            'harga_prediksi' => (float) $row['predicted_real'],
            'selisih' => (float) $row['selisih'],
            'error' => (float) $row['error'],
        ]);
    }
}
