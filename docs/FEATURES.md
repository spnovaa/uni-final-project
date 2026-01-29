# Gateway Features and Performance Notes

## Token Billing (Real Tokenizer)
- Token estimation uses the BPE tokenizer configured by model name.
- If a model is not recognized, the gateway falls back to the encoding in `GATEWAY_TOKENIZER_DEFAULT_ENCODING`.
- Estimated token counts are used to **pre-check** wallet balance before the provider call.
- Actual usage from provider responses is billed after the response is received.

## Non-Token Billing
The gateway supports non-token usage metrics alongside tokens. The pricing is driven by
`provider_models.pricing_config` (stored in SQL Server and cached):
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

## Provider Selection (Explicit)
Gateway requests must include **both** `provider` and `model` in the payload:
- Example: `{ "provider": "openai", "model": "gpt-4o-mini" }`
- Provider and model are validated against `providers` + `provider_models`.
- Requests with unknown provider/model return an OpenAI-style `invalid_request_error`.

## Gemini Support (OpenAI-Compatible Gateway)
The gateway can route to **Gemini** using the native Gemini API while keeping the
external API **OpenAI-compatible**:
- `chat.completions` and `responses` map to Gemini `generateContent`
- `embeddings` maps to Gemini `embedContent` / `batchEmbedContents`
- Gemini usage metadata is mapped to OpenAI-style `usage`

## OpenAI-Compatible Providers (Groq / OpenRouter)
The gateway can route to additional OpenAI-compatible providers by setting
`provider` to `groq` or `openrouter` and using the provider's model key.
Configure their base URLs and API keys in `.env` (see `.env.example`).

## Cache Service (Centralized)
A dedicated cache service is used to keep cache keys and TTLs consistent:
- Cache-aside for read-heavy resources: plans, providers, provider models, profile.
- TTLs are configured in `config/cache.php` under `ttls`.
- `provider_models` list cache is invalidated when models are created.
- `providers` list and provider config caches are invalidated when providers are created.
