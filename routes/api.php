<?php

use App\Http\Controllers\Api\V1\ApiClientController;
use App\Http\Controllers\Api\V1\ApiKeyController;
use App\Http\Controllers\Api\V1\AuthOtpController;
use App\Http\Controllers\Api\V1\Gateway\GatewayController;
use App\Http\Controllers\Api\V1\MeController;
use App\Http\Controllers\Api\V1\PlanController;
use App\Http\Controllers\Api\V1\ProviderController;
use App\Http\Controllers\Api\V1\ProviderModelController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->group(function () {
    Route::post('auth/otp/start', [AuthOtpController::class, 'start']);
    Route::post('auth/otp/verify', [AuthOtpController::class, 'verify']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [MeController::class, 'show']);
        Route::patch('me', [MeController::class, 'update']);

        Route::get('api-clients', [ApiClientController::class, 'index']);
        Route::post('api-clients', [ApiClientController::class, 'store']);

        Route::get('api-clients/{apiClient}/keys', [ApiKeyController::class, 'index']);
        Route::post('api-clients/{apiClient}/keys', [ApiKeyController::class, 'store']);
        Route::post('api-keys/{apiKey}/revoke', [ApiKeyController::class, 'revoke']);
        Route::post('api-keys/{apiKey}/rotate', [ApiKeyController::class, 'rotate']);

        Route::get('wallet', [WalletController::class, 'show']);
        Route::post('wallet/topup', [WalletController::class, 'topup']);
        Route::get('wallet/transactions', [WalletController::class, 'transactions']);

        Route::get('plans', [PlanController::class, 'index']);
        Route::post('plans', [PlanController::class, 'store']);

        Route::post('subscriptions', [SubscriptionController::class, 'store']);
        Route::get('subscriptions/current', [SubscriptionController::class, 'current']);
        Route::post('subscriptions/cancel', [SubscriptionController::class, 'cancel']);

        Route::get('providers', [ProviderController::class, 'index']);
        Route::post('providers', [ProviderController::class, 'store']);
        Route::get('providers/{provider}/models', [ProviderModelController::class, 'index']);
        Route::post('providers/{provider}/models', [ProviderModelController::class, 'store']);
    });

    Route::prefix('ai')->middleware('api.key')->group(function () {
        Route::post('responses', [GatewayController::class, 'responses']);
        Route::post('chat/completions', [GatewayController::class, 'chatCompletions']);
        Route::post('embeddings', [GatewayController::class, 'embeddings']);
        Route::post('images/generations', [GatewayController::class, 'imagesGenerations']);
        Route::post('audio/transcriptions', [GatewayController::class, 'audioTranscriptions']);
        Route::post('audio/speech', [GatewayController::class, 'audioSpeech']);
    });
});
