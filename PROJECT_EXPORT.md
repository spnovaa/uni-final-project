# Unified AI Gateway -- Full Project Context Export
Date: 2026-01-29
Location: project root

This document is a **full context export** of the project: requirements, patterns, architecture, data model, endpoints, configuration, and current state. It is intended to help you migrate this project to another Codex account with minimal loss of context.

---

## 1) Requirements (Compiled)
These are the combined requirements from `project_plan.md`, the signed proposal context, and follow-up instructions in this workspace.

### Core Functional Requirements
- **Unified AI gateway** that standardizes access to multiple AI providers.
- **OpenAI-compatible API surface** (same endpoints/shape).
- **Versioned REST API** under `/api/v1/...`.
- **Explicit provider + model selection** in every request (client chooses provider+model).
- **Provider switching** by just changing `provider` and `model` fields.
- **Wallet + subscription billing** with usage metering and invoices.
- **Usage reports** (usage, wallet ledger, invoices).
- **API key management** (create/list/rotate/revoke), API clients.
- **OTP auth** for user access.
- **Profile management** (update profile, update profile image).
- **Real tokenizer** for token estimation and billing.
- **Non-token billing** (images/audio).
- **Redis cache** for performance (cache-aside).
- **Swagger / OpenAPI docs** using L5-Swagger.
- **No front-end except Playground** (a test UI is allowed).
- **Windows compatible**.
- **Service, Repository, Resource layers**.
- **Feature + unit tests for every endpoint**.

### Operational Requirements
- **Queue-based background jobs** (Redis).
- **External log shipping** (not only DB) for scale.
- Switch between **multiple log services** with a single config change.
- **Free services supported** for non-financial usage.

---

## 2) Current Feature Coverage -- Status Matrix

### [OK] Implemented
- OpenAI-compatible gateway endpoints:
  - `/api/v1/ai/chat/completions`
  - `/api/v1/ai/responses`
  - `/api/v1/ai/embeddings`
  - `/api/v1/ai/images/generations`
  - `/api/v1/ai/audio/transcriptions`
  - `/api/v1/ai/audio/speech`
- Explicit **provider + model** validation (required).
- Provider adapters:
  - **OpenAI** (OpenAI-compatible)
  - **Gemini** (native Gemini API mapped to OpenAI response format)
  - **Groq** (OpenAI-compatible)
  - **OpenRouter** (OpenAI-compatible)
- Billing: wallet, subscriptions, invoices, usage metering & charging.
- Reports: usage, wallet ledger, invoices.
- API keys + clients with audit logs.
- OTP flow (throttle + challenge + verification).
- Profile management + image upload.
- Real tokenizer (BPE) with fallback encoding.
- Non-token billing (images, audio seconds, audio chars).
- Cache-aside for profile, plans, providers, provider models.
- External log shipping via queue (Loki + Better Stack).
- Vue playground at `/playground`.
- L5-Swagger docs configured and generated.
- Unit + feature tests across modules.

### [WARN] Not Implemented (Known)
- OTP delivery uses **log stub** (no real SMS/email provider).
- No real payment gateway integration (wallet top-up is manual).
- External log sink credentials are not configured by default.

---

## 3) Patterns & Architecture

### Structural Patterns
- **Service Layer**: business logic per domain (e.g., Billing, Provider, Reporting).
- **Repository Layer**: DB access abstraction.
- **Resource Layer**: API response shaping.
- **Pipeline Pattern**: Gateway request flow (Laravel Pipeline).

### Gateway Pipeline (Order)
`ResolveApiKey` -> `EnforceIpAllowlist` -> `ValidateGatewayPayload` -> `RateLimit`
-> `SelectProvider` -> `ValidateProviderSelection` -> `EstimateUsage`
-> `CheckSubscriptionOrWallet` -> `DispatchProviderRequest`
-> `NormalizeProviderResponse` -> `MeterUsage` -> `PersistLogs`
-> `ChargeUsage` -> `DispatchExternalLogs`

---

## 4) Directory Structure (Key Paths)
- `app/Domains/Gateway` -- gateway DTOs, services, logging
- `app/Domains/Providers` -- provider adapters (OpenAI, Gemini, etc.)
- `app/Pipelines/Gateway` -- pipeline pipes
- `app/Services/*` -- service layer
- `app/Repositories/*` -- repository layer
- `app/Http/Resources/*` -- API resources
- `app/Jobs/*` -- async jobs
- `routes/api.php` -- API routes
- `resources/js/playground` + `resources/views/playground.blade.php` -- Playground
- `docs/FEATURES.md` -- feature notes and cache/log settings

---

## 5) Data Model (DB Tables)
### Identity & Access
- `users`, `roles`, `permissions`, `role_user`, `permission_role`
- `otp_challenges`

### API Access
- `api_clients`
- `api_keys`

### Providers & Models
- `providers`
- `provider_models`
- `routing_rules` (present but unused by explicit selection)

### Billing & Subscription
- `wallets`
- `wallet_transactions`
- `subscription_plans`
- `subscriptions`
- `invoices`
- `invoice_items`

### Usage & Reporting
- `gateway_requests`
- `usage_records`
- `daily_usage_rollups`
- `audit_logs`
- `provider_health_checks`

---

## 6) Providers & API Behavior
### Required Request Fields
Every AI request must include:
```
{ "provider": "openai|gemini|groq|openrouter", "model": "..." }
```

### Supported Providers
- **OpenAI**: direct OpenAI API
- **Gemini**: mapped into OpenAI-style responses (chat/responses/embeddings only)
- **Groq**: OpenAI-compatible
- **OpenRouter**: OpenAI-compatible

### Example Gateway Call
```
POST /api/v1/ai/chat/completions
{
  "provider": "groq",
  "model": "allam-2-7b",
  "messages": [{"role":"user","content":"Hello"}]
}
```

---

## 7) Billing & Usage
- Pricing comes from **DB** (`provider_models.pricing_config`) and is cached.
- Token usage is estimated before call to enforce wallet balance.
- Actual usage from provider response is billed after response.
- Non-token metrics supported:
  - `image_cost_per_unit`
  - `audio_cost_per_second`
  - `audio_cost_per_char`

---

## 8) Cache Strategy
Cache-aside for read-heavy data:
- Plans (`plans:active`)
- Providers (`providers:all`)
- Provider configs (`providers:config:{name}`)
- Provider models (`provider_models:list:{providerId}` + `provider_models:{provider}:{model}`)
- User profiles (`user:profile:{id}`)

TTL configured in `config/cache.php` via env vars:
- `PLAN_CACHE_TTL`, `PROVIDER_CACHE_TTL`, `PROVIDER_MODEL_CACHE_TTL`, `PROVIDER_CONFIG_CACHE_TTL`, `PROFILE_CACHE_TTL`

---

## 9) External Logging (Queue + Switchable Sinks)
- Logs are shipped asynchronously by `DispatchGatewayLogJob`.
- Enable by setting:
  - `GATEWAY_LOG_SINK=loki` **or** `GATEWAY_LOG_SINK=betterstack`
- Two free-tier friendly sinks are implemented:
  - **Grafana Loki** (`LOKI_PUSH_URL`, `LOKI_USERNAME`, `LOKI_API_KEY`)
  - **Better Stack Logs** (`BETTERSTACK_INGEST_HOST`, `BETTERSTACK_SOURCE_TOKEN`)
- Payloads are redacted + truncated (`GATEWAY_LOG_MAX_BYTES`).

---

## 10) Swagger / OpenAPI
- L5-Swagger is configured with schemas under `app/Http/Resources/*`.
- Generate docs:
```
php artisan l5-swagger:generate
```

---

## 11) Playground
- Vue 3 + Vite UI at `/playground`.
- Allows selecting endpoint, provider, model, payload, and API key.

---

## 12) Tests
- Full test suite passes.
- **Feature tests** for all endpoints.
- **Unit tests** for core services, cache policies, log sinks, tokenizer.

Run:
```
php artisan test
```

---

## 13) How To Run (Windows)
1) Install dependencies
```
composer install
npm install
```
2) Configure `.env`
- SQL Server + Redis
- Provider keys (OpenAI/Gemini/Groq/OpenRouter)
3) Run migrations
```
php artisan migrate
```
4) Build playground
```
npm run build
```
5) Start app + queue worker
```
php artisan serve
php artisan queue:work
```

---

## 14) Known Caveats
- OTP sending is stubbed (logs only). No real SMS/email provider.
- SQL Server issue was fixed by commenting `PDO::ATTR_STRINGIFY_FETCHES` in `vendor/laravel/framework/.../SqlServerConnector.php`. This is **not committed** and must be re-applied after fresh install.
- `.env` may contain real provider keys; review before sharing.

---

## 15) Environment Variables (Summary)
**DB**
- `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

**Redis**
- `REDIS_HOST`, `REDIS_PORT`, `REDIS_PASSWORD`, `REDIS_CLIENT`

**Providers**
- `OPENAI_*`, `GEMINI_*`, `GROQ_*`, `OPENROUTER_*`

**Cache TTLs**
- `PLAN_CACHE_TTL`, `PROVIDER_CACHE_TTL`, `PROVIDER_MODEL_CACHE_TTL`, `PROVIDER_CONFIG_CACHE_TTL`, `PROFILE_CACHE_TTL`

**Logging**
- `GATEWAY_LOG_SINK`, `GATEWAY_LOG_MAX_BYTES`
- `LOKI_*`, `BETTERSTACK_*`

---

## 16) Git / Branch History (Key Merges)
- core services, models, billing, audit logs, invoices, reporting, gateway billing
- playground, tokenizer, non-token pricing, profile caching
- provider explicit selection + Gemini adapter
- cache service + policies
- Groq/OpenRouter support
- external log shipping

---

## 17) Files of Interest
- Gateway logging: `app/Domains/Gateway/Logging/*`, `app/Jobs/DispatchGatewayLogJob.php`
- Provider adapters: `app/Domains/Providers/*`
- Pipeline: `app/Pipelines/Gateway/*`
- Swagger: `app/Http/Controllers/Api/V1/DocsController.php`
- Playground: `resources/js/playground/App.vue`, `resources/views/playground.blade.php`
- Docs: `docs/FEATURES.md`

---

If you need a **Postman collection** export for all endpoints, I can generate it.
