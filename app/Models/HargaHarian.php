<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HargaHarian extends Model
{
    protected $table = 'harga_harian';

    protected $fillable = [
        'region',
        'tanggal',
        'harga'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'harga' => 'integer'
    ];
}
