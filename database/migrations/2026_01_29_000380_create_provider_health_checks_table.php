<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('provider_health_checks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('provider_id');
            $table->string('status')->default('unknown');
            $table->unsignedInteger('latency_ms')->nullable();
            $table->timestamp('checked_at')->useCurrent();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->index(['provider_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_health_checks');
    }
};
