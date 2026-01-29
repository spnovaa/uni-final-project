<?php

namespace App\Jobs;

use App\Domains\Gateway\Logging\LogSinkManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use Illuminate\Support\Facades\Log;

class DispatchGatewayLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $sink,
        public array $payload
    ) {
    }

    public function handle(LogSinkManager $manager): void
    {
        try {
            $manager->resolve($this->sink)->send($this->payload);
        } catch (Throwable $exception) {
            Log::warning('External log sink failed', [
                'sink' => $this->sink,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
