<?php

namespace App\Http\Controllers\Api\V1\Gateway;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Services\GatewayService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * API controller for gateway endpoints.
 */
class GatewayController extends Controller
{
    /**
     * Create a new instance.
     * @param GatewayService $service
     * @return void
     */
    public function __construct(private readonly GatewayService $service)
    {
    }

    /**
     * Handle OpenAI-compatible chat completions requests.
     *
     * The request must include explicit `provider` and `model` fields to select the upstream.
     * @param Request $request
     * @return mixed
     */
    public function chatCompletions(Request $request)
    {
        return $this->handle($request, 'chat.completions');
    }

    /**
     * Handle OpenAI-compatible responses requests.
     * @param Request $request
     * @return mixed
     */
    public function responses(Request $request)
    {
        return $this->handle($request, 'responses');
    }

    /**
     * Handle OpenAI-compatible embeddings requests.
     * @param Request $request
     * @return mixed
     */
    public function embeddings(Request $request)
    {
        return $this->handle($request, 'embeddings');
    }

    /**
     * Handle OpenAI-compatible image generation requests.
     * @param Request $request
     * @return mixed
     */
    public function imagesGenerations(Request $request)
    {
        return $this->handle($request, 'images.generations');
    }

    /**
     * Handle OpenAI-compatible audio transcription requests.
     *
     * Requires a multipart `file` upload.
     * @param Request $request
     * @return mixed
     */
    public function audioTranscriptions(Request $request)
    {
        return $this->handle($request, 'audio.transcriptions');
    }

    /**
     * Handle OpenAI-compatible text-to-speech requests.
     *
     * This endpoint may return a binary audio response.
     * @param Request $request
     * @return mixed
     */
    public function audioSpeech(Request $request)
    {
        return $this->handle($request, 'audio.speech');
    }

    /**
     * Build a gateway request context and delegate to the gateway service.
     *
     * The gateway service runs validation, routing, provider dispatch, normalization, billing,
     * logging, and response shaping via a pipeline.
     * @param Request $request
     * @param string $endpoint
     * @return mixed
     */
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
