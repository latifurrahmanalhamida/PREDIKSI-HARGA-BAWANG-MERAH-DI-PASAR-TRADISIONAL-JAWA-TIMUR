<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('prediction_logs', function (Blueprint $table) {
            $table->id();
            $table->string('region', 50);
            $table->json('input_prices'); // [day1, day2, .. ., day7]
            $table->decimal('predicted_price', 12, 2);
            $table->decimal('confidence_score', 5, 2)->nullable();
            $table->integer('runtime_ms');
            $table->decimal('mape_score', 5, 4)->nullable();
            $table->string('trend_direction', 10)->nullable(); // up, down, stable
            $table->decimal('trend_percentage', 5, 2)->nullable();
            $table->timestamp('predicted_for_date');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['region', 'created_at']);
            $table->index('predicted_for_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('prediction_logs');
    }
};