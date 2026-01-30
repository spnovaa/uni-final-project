<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Send an OTP code to the requested destination.
 *
 * This project currently implements OTP delivery as a stub (logs the event). Replace the
 * implementation with an SMS/email provider integration in production.
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
     * Perform OTP delivery (currently a log-only stub).
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
