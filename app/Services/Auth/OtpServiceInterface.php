<?php

namespace App\Services\Auth;

use App\Domains\Auth\DTOs\OtpContext;
use App\Models\User;

/**
 * Service layer for otp.
 */
interface OtpServiceInterface
{
    /**
     * Start.
     * @param string $destination
     * @param string $channel
     * @param ?string $ip
     * @return OtpContext
     */
    public function start(string $destination, string $channel, ?string $ip = null): OtpContext;

    /**
     * @return array{ok:bool,message?:string,status:int,user?:User}
     */
    public function verify(string $destination, string $code, ?string $channel = null): array;
}
