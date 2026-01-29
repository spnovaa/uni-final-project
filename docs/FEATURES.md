# Gateway Features and Performance Notes

## Token Billing (Real Tokenizer)
- Token estimation uses the BPE tokenizer configured by model name.
- If a model is not recognized, the gateway falls back to the encoding in `GATEWAY_TOKENIZER_DEFAULT_ENCODING`.
- Estimated token counts are used to **pre-check** wallet balance before the provider call.
- Actual usage from provider responses is billed after the response is received.

## Non-Token Billing
The gateway supports non-token usage metrics alongside tokens. The pricing is driven by
`provider_models.pricing_config`:
- `image_cost_per_unit`: cost per generated image
- `audio_cost_per_second`: cost per audio second (transcription or TTS)
- `audio_cost_per_char`: cost per input character for TTS

Estimation details:
- Images: uses `n`, `num_images`, or `batch_size` from the request (defaults to 1).
- Audio transcription: uses `duration_seconds` if supplied, otherwise estimates by file size
  using `GATEWAY_AUDIO_BYTES_PER_SECOND`.
- Audio speech (TTS): uses input character count to estimate cost.

## Profile Caching (Cache-Aside)
User profiles are cached using a cache-aside strategy:
- Cache key: `user:profile:{user_id}`
- TTL: `PROFILE_CACHE_TTL` (seconds), default 300
- Cache is **invalidated** immediately after profile updates or profile image uploads.

## Profile Image Management
Profile updates accept a `profile_image` file on `PATCH /api/v1/me`:
- Stored on the `public` disk under `profile_images/{user_id}`
- Previous profile image is deleted on update
- API response returns `profile_image_url`

## Playground (Vue + Vite)
The playground UI is a Vue app served at `/playground`:
- Built with Vite: `npm run dev` (local) or `npm run build` (production)
- Blade view loads Vite assets, but skips Vite in testing to avoid missing manifest errors

