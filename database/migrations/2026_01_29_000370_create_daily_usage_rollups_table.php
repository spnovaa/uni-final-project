<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('daily_usage_rollups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('date');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('api_key_id')->nullable();
            $table->unsignedBigInteger('provider_id')->nullable();
            $table->unsignedBigInteger('provider_model_id')->nullable();
            $table->string('metric');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('total_cost', 12, 4)->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('api_key_id')->references('id')->on('api_keys');
            $table->foreign('provider_id')->references('id')->on('providers');
            $table->foreign('provider_model_id')->references('id')->on('provider_models');

            $table->unique([
                'date',
                'user_id',
                'api_key_id',
                'provider_id',
                'provider_model_id',
                'metric',
            ], 'daily_usage_rollups_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_usage_rollups');
    }
};
