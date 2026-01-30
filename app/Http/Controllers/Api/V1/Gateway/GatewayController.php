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
     * Chat completions.
     * @param Request $request
     * @return mixed
     */
    public function chatCompletions(Request $request)
    {
        return $this->handle($request, 'chat.completions');
    }

    /**
     * Responses.
     * @param Request $request
     * @return mixed
     */
    public function responses(Request $request)
    {
        return $this->handle($request, 'responses');
    }

    /**
     * Embeddings.
     * @param Request $request
     * @return mixed
     */
    public function embeddings(Request $request)
    {
        return $this->handle($request, 'embeddings');
    }

    /**
     * Images generations.
     * @param Request $request
     * @return mixed
     */
    public function imagesGenerations(Request $request)
    {
        return $this->handle($request, 'images.generations');
    }

    /**
     * Audio transcriptions.
     * @param Request $request
     * @return mixed
     */
    public function audioTranscriptions(Request $request)
    {
        return $this->handle($request, 'audio.transcriptions');
    }

    /**
     * Audio speech.
     * @param Request $request
     * @return mixed
     */
    public function audioSpeech(Request $request)
    {
        return $this->handle($request, 'audio.speech');
    }

    /**
     * Handle an OpenAI-compatible gateway request.
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
