<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('model_key');
            $table->json('capabilities')->nullable();
            $table->json('pricing_config')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['provider_id', 'model_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_models');
    }
};
