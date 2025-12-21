<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('data_uji_prediksi', function (Blueprint $table) {
            $table->id();
            $table->string('region', 30);
            $table->date('tanggal');
            $table->double('harga_aktual');
            $table->double('harga_prediksi');
            $table->double('selisih');
            $table->double('error'); // error (%) – bisa juga decimal
            $table->timestamps();
            $table->unique(['region', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_uji_prediksi');
    }
};
