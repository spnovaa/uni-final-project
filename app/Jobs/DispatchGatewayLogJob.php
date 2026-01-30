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

/**
 * Deliver a gateway log payload to a configured external log sink.
 *
 * This job is best-effort: failures are logged as warnings and do not affect API responses.
 */
class DispatchGatewayLogJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new instance.
     * @param string $sink
     * @param array $payload
     * @return void
     */
    public function __construct(
        public string $sink,
        public array $payload
    ) {
    }

    /**
     * Send the log payload to the selected sink.
     *
     * Any exception is caught and logged to avoid breaking the queue worker.
     * @param LogSinkManager $manager
     * @return void
     */
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
