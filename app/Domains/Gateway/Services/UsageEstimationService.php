<?php

namespace App\Domains\Gateway\Services;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\DTOs\UsageMetrics;
use Danny50610\BpeTokeniser\EncodingFactory;
use InvalidArgumentException;

class UsageEstimationService
{
    public function estimate(GatewayRequestContext $context): UsageMetrics
    {
        $promptTokens = 0;
        $completionTokens = 0;
        $model = $context->modelKey;

        switch ($context->endpoint) {
            case 'chat.completions':
                $promptTokens = $this->estimateTokens($this->extractChatText($context->payload), $model);
                $completionTokens = $this->extractMaxOutputTokens($context->payload);
                break;
            case 'responses':
                $promptTokens = $this->estimateTokens($this->extractResponsesText($context->payload), $model);
                $completionTokens = $this->extractMaxOutputTokens($context->payload);
                break;
            case 'embeddings':
                $promptTokens = $this->estimateTokens($this->extractEmbeddingsText($context->payload), $model);
                break;
            case 'images.generations':
                $promptTokens = $this->estimateTokens((string) ($context->payload['prompt'] ?? ''), $model);
                break;
            case 'audio.speech':
                $promptTokens = $this->estimateTokens((string) ($context->payload['input'] ?? ''), $model);
                break;
            case 'audio.transcriptions':
            default:
                $promptTokens = 0;
                $completionTokens = 0;
                break;
        }

        $total = ($promptTokens + $completionTokens) ?: null;

        return new UsageMetrics(
            $promptTokens ?: null,
            $completionTokens ?: null,
            $total
        );
    }

    private function estimateTokens(string $text, ?string $model = null): int
    {
        if ($text === '') {
            return 0;
        }

        $encoding = $this->resolveEncoding($model);

        return count($encoding->encode($text));
    }

    private function resolveEncoding(?string $model = null)
    {
        $defaultEncoding = config('gateway.tokenizer_default_encoding', 'cl100k_base');

        try {
            if ($model) {
                return EncodingFactory::createByModelName($model);
            }
        } catch (InvalidArgumentException $exception) {
            // Fall back to default encoding if model mapping is unknown.
        }

        return EncodingFactory::createByEncodingName($defaultEncoding);
    }

    private function extractChatText(array $payload): string
    {
        $messages = $payload['messages'] ?? [];
        $parts = [];

        foreach ($messages as $message) {
            if (! is_array($message)) {
                continue;
            }

            $content = $message['content'] ?? null;
            if (is_string($content)) {
                $parts[] = $content;
                continue;
            }

            if (is_array($content)) {
                foreach ($content as $item) {
                    if (is_array($item)) {
                        $parts[] = (string) ($item['text'] ?? $item['input_text'] ?? $item['content'] ?? '');
                    } elseif (is_string($item)) {
                        $parts[] = $item;
                    }
                }
            }
        }

        return implode(' ', $parts);
    }

    private function extractResponsesText(array $payload): string
    {
        $input = $payload['input'] ?? null;

        if (is_string($input)) {
            return $input;
        }

        if (is_array($input)) {
            return $this->extractTextFromArray($input);
        }

        return '';
    }

    private function extractEmbeddingsText(array $payload): string
    {
        $input = $payload['input'] ?? null;

        if (is_string($input)) {
            return $input;
        }

        if (is_array($input)) {
            $parts = [];
            foreach ($input as $item) {
                if (is_string($item)) {
                    $parts[] = $item;
                }
            }

            return implode(' ', $parts);
        }

        return '';
    }

    private function extractTextFromArray(array $input): string
    {
        $parts = [];

        foreach ($input as $item) {
            if (is_string($item)) {
                $parts[] = $item;
                continue;
            }

            if (is_array($item)) {
                if (isset($item['content']) && is_string($item['content'])) {
                    $parts[] = $item['content'];
                }

                if (isset($item['text']) && is_string($item['text'])) {
                    $parts[] = $item['text'];
                }

                if (isset($item['input_text']) && is_string($item['input_text'])) {
                    $parts[] = $item['input_text'];
                }

                if (isset($item['content']) && is_array($item['content'])) {
                    $parts[] = $this->extractTextFromArray($item['content']);
                }
            }
        }

        return implode(' ', array_filter($parts));
    }

    private function extractMaxOutputTokens(array $payload): int
    {
        $keys = ['max_output_tokens', 'max_completion_tokens', 'max_tokens'];
        foreach ($keys as $key) {
            if (isset($payload[$key]) && is_numeric($payload[$key])) {
                return (int) $payload[$key];
            }
        }

        return 0;
    }
}
