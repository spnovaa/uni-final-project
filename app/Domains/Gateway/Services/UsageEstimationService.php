<?php

namespace App\Domains\Gateway\Services;

use App\Domains\Gateway\DTOs\GatewayRequestContext;
use App\Domains\Gateway\DTOs\UsageMetrics;
use Danny50610\BpeTokeniser\EncodingFactory;
use InvalidArgumentException;

/**
 * Service layer for usage estimation.
 */
class UsageEstimationService
{
    /**
     * Estimate.
     * @param GatewayRequestContext $context
     * @return UsageMetrics
     */
    public function estimate(GatewayRequestContext $context): UsageMetrics
    {
        $promptTokens = 0;
        $completionTokens = 0;
        $model = $context->modelKey;
        $images = null;
        $audioSeconds = null;
        $audioCharacters = null;

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
                $images = $this->extractImageCount($context->payload);
                break;
            case 'audio.speech':
                $promptTokens = $this->estimateTokens((string) ($context->payload['input'] ?? ''), $model);
                $audioCharacters = $this->estimateCharacters((string) ($context->payload['input'] ?? ''));
                break;
            case 'audio.transcriptions':
                $audioSeconds = $this->estimateAudioSeconds($context);
                $promptTokens = 0;
                $completionTokens = 0;
                break;
            default:
                $promptTokens = 0;
                $completionTokens = 0;
                break;
        }

        $total = ($promptTokens + $completionTokens) ?: null;

        return new UsageMetrics(
            $promptTokens ?: null,
            $completionTokens ?: null,
            $total,
            $images,
            $audioSeconds,
            $audioCharacters,
        );
    }

    /**
     * Estimate tokens.
     * @param string $text
     * @param ?string $model
     * @return int
     */
    private function estimateTokens(string $text, ?string $model = null): int
    {
        if ($text === '') {
            return 0;
        }

        $encoding = $this->resolveEncoding($model);

        return count($encoding->encode($text));
    }

    /**
     * Resolve encoding.
     * @param ?string $model
     * @return mixed
     */
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

    /**
     * Extract chat text.
     * @param array $payload
     * @return string
     */
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

    /**
     * Extract responses text.
     * @param array $payload
     * @return string
     */
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

    /**
     * Extract embeddings text.
     * @param array $payload
     * @return string
     */
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

    /**
     * Extract text from array.
     * @param array $input
     * @return string
     */
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

    /**
     * Extract max output tokens.
     * @param array $payload
     * @return int
     */
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

    /**
     * Extract image count.
     * @param array $payload
     * @return int
     */
    private function extractImageCount(array $payload): int
    {
        $candidates = ['n', 'num_images', 'batch_size'];
        foreach ($candidates as $key) {
            if (isset($payload[$key]) && is_numeric($payload[$key])) {
                return max(1, (int) $payload[$key]);
            }
        }

        return 1;
    }

    /**
     * Estimate characters.
     * @param string $text
     * @return int
     */
    private function estimateCharacters(string $text): int
    {
        return mb_strlen($text, 'UTF-8');
    }

    /**
     * Estimate audio seconds.
     * @param GatewayRequestContext $context
     * @return ?float
     */
    private function estimateAudioSeconds(GatewayRequestContext $context): ?float
    {
        if (isset($context->payload['duration_seconds']) && is_numeric($context->payload['duration_seconds'])) {
            return (float) $context->payload['duration_seconds'];
        }

        $bytes = $this->extractFileSize($context->files);
        if ($bytes === null) {
            return null;
        }

        $bytesPerSecond = (int) config('gateway.audio_bytes_per_second', 16000);
        $bytesPerSecond = $bytesPerSecond > 0 ? $bytesPerSecond : 16000;

        return round($bytes / $bytesPerSecond, 2);
    }

    /**
     * Extract file size.
     * @param array $files
     * @return ?int
     */
    private function extractFileSize(array $files): ?int
    {
        foreach ($files as $file) {
            if ($file instanceof \Illuminate\Http\UploadedFile) {
                return $file->getSize() ?: null;
            }
        }

        return null;
    }
}
