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
        Schema::create('harga_harian', function (Blueprint $table) {
            $table->id();
            $table->string('region', 50);
            $table->date('tanggal');
            $table->integer('harga');
            $table->timestamps();

            $table->unique(['region', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('harga_harian');
    }
};
