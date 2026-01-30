<?php

namespace App\Services\Auth;

use App\Domains\Auth\DTOs\OtpContext;
use App\Models\User;

/**
 * OTP service contract.
 *
 * Exposes the start/verify operations used by the OTP controller.
 */
interface OtpServiceInterface
{
    /**
     * Start an OTP challenge for a destination/channel.
     * @param string $destination
     * @param string $channel
     * @param ?string $ip
     * @return OtpContext
     */
    public function start(string $destination, string $channel, ?string $ip = null): OtpContext;

    /**
     * Verify an OTP code and return an authentication result.
     *
     * @return array{ok:bool,message?:string,status:int,user?:User}
     */
    public function verify(string $destination, string $code, ?string $channel = null): array;
}
