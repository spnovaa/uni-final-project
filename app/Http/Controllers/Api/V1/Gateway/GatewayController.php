<?php

namespace App\Http\Controllers\Api\V1\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Services\GatewayService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GatewayController extends Controller
{
    public function __construct(private readonly GatewayService $service)
    {
    }

    public function chatCompletions(Request $request)
    {
        return $this->handle($request, 'chat.completions');
    }

    public function responses(Request $request)
    {
        return $this->handle($request, 'responses');
    }

    public function embeddings(Request $request)
    {
        return $this->handle($request, 'embeddings');
    }

    public function imagesGenerations(Request $request)
    {
        return $this->handle($request, 'images.generations');
    }

    public function audioTranscriptions(Request $request)
    {
        return $this->handle($request, 'audio.transcriptions');
    }

    public function audioSpeech(Request $request)
    {
        return $this->handle($request, 'audio.speech');
    }

    private function handle(Request $request, string $endpoint)
    {
        $payload = $request->all();
        $files = $request->allFiles();

        $context = new GatewayRequestContext(
            $request,
            $endpoint,
            $payload,
            $files,
        );

        return $this->service->handle($context);
    }
}
