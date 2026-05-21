# Postman Assets

This folder contains Postman assets prepared for capturing execution screenshots of the Unified AI Gateway.

## Files

- `Unified AI Gateway - Full API.postman_collection.json`: full collection covering all 35 versioned API routes
- `Unified AI Gateway - Screenshot Flow.postman_collection.json`: shorter scenario-oriented flow for thesis screenshots
- `Unified AI Gateway - Local.postman_environment.json`: local environment variables used by both collections

## Suggested local preparation

1. Start the application:

```bash
php artisan serve
```

2. Ensure the development database schema exists:

```bash
php artisan migrate
```

3. If your queue worker is part of the demo, run it separately:

```bash
php artisan queue:listen --tries=1 --timeout=0
```

## OTP note

The current OTP delivery job is a development stub and does not expose the generated code in the API response.

For deterministic local screenshots, a practical workflow is:

1. Run `Start OTP` from Postman.
2. Replace the latest OTP challenge hash with a known code such as `123456` via Tinker:

```bash
php artisan tinker
```

```php
use App\Models\OtpChallenge;
use Illuminate\Support\Facades\Hash;

OtpChallenge::query()
    ->where('destination', 'user@example.com')
    ->latest('id')
    ->first()
    ?->update([
        'code_hash' => Hash::make('123456'),
        'expires_at' => now()->addMinutes(10),
        'attempts' => 0,
    ]);
```

3. Run `Verify OTP` using `123456`.

## AI endpoint note

AI gateway requests require a valid project API key created through the dashboard endpoints and a valid upstream provider configuration. The gateway can use provider credentials either from `.env` or from records created through the provider endpoints.
