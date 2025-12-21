<?php

namespace App\Imports;

use App\Models\HargaHarian;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class HargaHarianImport implements ToModel, WithHeadingRow
{
    protected $region;

    public function __construct($region)
    {
        $this->region = $region;
    }

    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['tanggal']) || empty($row['harga_rp'])) {
            return null;
        }

        // Parse tanggal - handle various date formats
        $tanggal = $row['tanggal'];
        if (is_numeric($tanggal)) {
            // Excel serial date
            $tanggal = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($tanggal));
        } else {
            $tanggal = Carbon::parse($tanggal);
        }

        return new HargaHarian([
            'region' => $this->region,
            'tanggal' => $tanggal->format('Y-m-d'),
            'harga' => (int) $row['harga_rp'],
        ]);
    }
}