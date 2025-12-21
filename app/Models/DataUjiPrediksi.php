<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataUjiPrediksi extends Model
{
    protected $table = 'data_uji_prediksi';

    protected $fillable = [
        'region',
        'tanggal',
        'harga_aktual',
        'harga_prediksi',
        'selisih',
        'error'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'harga_aktual' => 'double',
        'harga_prediksi' => 'double',
        'selisih' => 'double',
        'error' => 'double',
    ];
}
