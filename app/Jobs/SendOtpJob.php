<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $destination,
        public string $channel,
        public string $code,
    ) {
    }

    public function handle(): void
    {
        Log::info('OTP dispatched', [
            'channel' => $this->channel,
            'destination' => $this->destination,
        ]);
    }
}
