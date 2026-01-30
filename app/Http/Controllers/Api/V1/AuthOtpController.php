<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use App\Services\Auth\OtpServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * API controller for auth otp endpoints.
 */
class AuthOtpController extends Controller
{
    /**
     * Create a new instance.
     * @param OtpServiceInterface $otpService
     * @return void
     */
    public function __construct(private readonly OtpServiceInterface $otpService)
    {
    }

    /**
     * Start an OTP challenge.
     * @param Request $request
     * @return mixed
     */
    public function start(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'channel' => ['required', Rule::in(['email', 'sms'])],
            'destination' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $channel = $request->input('channel');
        $destination = $request->input('destination');

        if ($channel === 'email' && ! filter_var($destination, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'message' => 'Invalid email address.',
            ], 422);
        }

        $context = $this->otpService->start($destination, $channel, $request->ip());

        if (! $context->ok()) {
            return response()->json([
                'message' => $context->errors[0] ?? 'OTP request failed.',
            ], $context->status);
        }

        return response()->json([
            'status' => 'sent',
        ]);
    }

    /**
     * Verify an OTP challenge and issue an access token.
     * @param Request $request
     * @return mixed
     */
    public function verify(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'destination' => ['required', 'string'],
            'code' => ['required', 'string'],
            'channel' => ['nullable', Rule::in(['email', 'sms'])],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $result = $this->otpService->verify(
            $request->input('destination'),
            $request->input('code'),
            $request->input('channel'),
        );

        if (! $result['ok']) {
            return response()->json([
                'message' => $result['message'],
            ], $result['status']);
        }

        $user = $result['user'];
        $token = $user->createToken('dashboard')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => UserResource::make($user),
        ]);
    }
}
