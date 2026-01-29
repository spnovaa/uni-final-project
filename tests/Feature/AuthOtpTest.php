<?php

namespace Tests\Feature;

use App\Jobs\SendOtpJob;
use App\Models\OtpChallenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AuthOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_start_otp(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/auth/otp/start', [
            'channel' => 'email',
            'destination' => 'user@example.com',
        ]);

        $response->assertOk()
            ->assertJson(['status' => 'sent']);

        Queue::assertPushed(SendOtpJob::class);
    }

    public function test_can_verify_otp_and_receive_token(): void
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);

        OtpChallenge::query()->create([
            'user_id' => $user->id,
            'channel' => 'email',
            'destination' => 'user@example.com',
            'code_hash' => Hash::make('123456'),
            'expires_at' => now()->addMinutes(10),
            'attempts' => 0,
        ]);

        $response = $this->postJson('/api/v1/auth/otp/verify', [
            'destination' => 'user@example.com',
            'code' => '123456',
            'channel' => 'email',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user']);
    }
}
