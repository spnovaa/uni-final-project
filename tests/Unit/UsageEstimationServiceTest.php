<?php

namespace Tests\Unit;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Services\UsageEstimationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class UsageEstimationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_estimates_chat_tokens_from_messages(): void
    {
        config(['gateway.token_chars_per_token' => 4]);
        $service = new UsageEstimationService();

        $context = new GatewayRequestContext(
            Request::create('/api/v1/ai/chat/completions', 'POST'),
            'chat.completions',
            [
                'messages' => [
                    ['role' => 'user', 'content' => 'abcd'],
                ],
                'max_tokens' => 5,
            ],
            []
        );

        $usage = $service->estimate($context);

        $this->assertSame(1, $usage->promptTokens);
        $this->assertSame(5, $usage->completionTokens);
    }

    public function test_estimates_embeddings_tokens_from_input_array(): void
    {
        config(['gateway.token_chars_per_token' => 4]);
        $service = new UsageEstimationService();

        $context = new GatewayRequestContext(
            Request::create('/api/v1/ai/embeddings', 'POST'),
            'embeddings',
            [
                'input' => ['abcd', 'efgh'],
            ],
            []
        );

        $usage = $service->estimate($context);

        $this->assertSame(3, $usage->promptTokens);
        $this->assertNull($usage->completionTokens);
    }
}
