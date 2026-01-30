<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Queued job for send otp.
 */
class SendOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new instance.
     * @param string $destination
     * @param string $channel
     * @param string $code
     * @return void
     */
    public function __construct(
        public string $destination,
        public string $channel,
        public string $code,
    ) {
    }

    /**
     * Handle the queued job.
     * @return void
     */
    public function handle(): void
    {
        Log::info('OTP dispatched', [
            'channel' => $this->channel,
            'destination' => $this->destination,
        ]);
    }
}
