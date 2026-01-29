<?php

namespace Tests\Unit;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\Services\UsageEstimationService;
use Danny50610\BpeTokeniser\EncodingFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class UsageEstimationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_estimates_chat_tokens_from_messages(): void
    {
        $service = new UsageEstimationService();
        $encoding = EncodingFactory::createByModelName('gpt-4o-mini');

        $context = new GatewayRequestContext(
            Request::create('/api/v1/ai/chat/completions', 'POST'),
            'chat.completions',
            [
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'user', 'content' => 'abcd'],
                ],
                'max_tokens' => 5,
            ],
            []
        );

        $usage = $service->estimate($context);

        $this->assertSame(count($encoding->encode('abcd')), $usage->promptTokens);
        $this->assertSame(5, $usage->completionTokens);
    }

    public function test_estimates_embeddings_tokens_from_input_array(): void
    {
        $service = new UsageEstimationService();
        $encoding = EncodingFactory::createByModelName('gpt-4o-mini');

        $context = new GatewayRequestContext(
            Request::create('/api/v1/ai/embeddings', 'POST'),
            'embeddings',
            [
                'model' => 'gpt-4o-mini',
                'input' => ['abcd', 'efgh'],
            ],
            []
        );

        $usage = $service->estimate($context);

        $this->assertSame(
            count($encoding->encode('abcd efgh')),
            $usage->promptTokens
        );
        $this->assertNull($usage->completionTokens);
    }
}
