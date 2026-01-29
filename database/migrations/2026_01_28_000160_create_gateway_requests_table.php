<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gateway_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_key_id')->nullable()->constrained('api_keys')->noActionOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->noActionOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained()->noActionOnDelete();
            $table->foreignId('provider_model_id')->nullable()->constrained()->noActionOnDelete();
            $table->string('endpoint');
            $table->string('request_hash')->nullable()->index();
            $table->string('status')->default('pending');
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_requests');
    }
};
