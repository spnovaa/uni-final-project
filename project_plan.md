# Unified AI Service Integration Gateway — Implementation Plan (Laravel + SQL Server + Redis)

> **Scope (from proposal):** build a backend “gateway” that sits between clients and multiple AI providers, offering **standardized APIs** plus **authentication, user/account management, wallet, subscription purchase, invoice issuance, API-key management, and usage reports**. fileciteturn0file0  
> **Technology updates (your changes):** **Laravel** backend, **SQL Server** database, **Redis** for **cache + queues**; apply **Pipeline** design pattern in implementation.

---

## 1) System Overview

### 1.1 Goals
- Provide a **single, stable API** for clients to call AI features (chat, embeddings, image generation, speech, etc.) while the gateway routes to different providers.
- Centralize: **Auth**, **API keys**, **billing/wallet**, **subscriptions**, **invoices**, **usage metering**, and **reporting**. fileciteturn0file0
- Allow **easy provider switching** without rewriting client code (provider abstraction). fileciteturn0file0

### 1.2 Actors
- **End User** (human): signs up, manages wallet/subscription, views invoices/usage.
- **Developer / Client App**: uses API keys to send AI requests.
- **Admin**: manages plans, pricing, providers, and audits.
- **AI Providers**: OpenAI/Anthropic/Mistral/etc. (pluggable). fileciteturn0file0
- **OTP Delivery Provider**: email/SMS service for one-time codes. fileciteturn0file0
- **Payment Provider** (optional but typical): for wallet top-ups/subscriptions.

### 1.3 High-level Components
1. **Public REST API (Laravel)**  
   - Versioned endpoints: `/api/v1/...`
2. **Gateway Core**
   - Provider abstraction + request/response normalization
3. **Billing Core**
   - Wallet + subscriptions + invoices
4. **Usage & Reporting**
   - Metering, logs, analytics, exports
5. **Redis**
   - Cache, rate-limit counters, and queues
6. **SQL Server**
   - Source-of-truth for accounts, keys, billing, logs

---

## 2) Architecture & Data Flow

### 2.1 Request Flow (AI call)
Client → **Gateway API** → Pipeline:
1) Authenticate (API key / bearer)  
2) Rate limit & quota checks  
3) Validate request payload  
4) Resolve provider + model  
5) Transform to provider format  
6) Send request (HTTP client)  
7) Normalize provider response  
8) Meter usage (tokens, seconds, images)  
9) Create usage log + billing records  
10) Return response to client

### 2.2 Pipeline Pattern (Mandatory)
Use Laravel’s `Illuminate\Pipeline\Pipeline` for core flows.

**Example: `GatewayRequestPipeline`**
- `ResolveClientPipe`
- `AuthenticateApiKeyPipe`
- `EnforceIpAllowlistPipe` (optional)
- `ValidateGatewayPayloadPipe`
- `RateLimitPipe` (Redis)
- `CheckSubscriptionOrWalletPipe`
- `SelectProviderPipe`
- `TransformToProviderRequestPipe`
- `DispatchProviderRequestPipe`
- `NormalizeProviderResponsePipe`
- `MeterUsagePipe`
- `PersistLogsPipe`
- `FinalizeResponsePipe`

You will also use pipelines for:
- **Auth (OTP) pipeline** (validate → throttle → send OTP → persist challenge)
- **Billing pipeline** (validate → create invoice → charge wallet/payment → finalize)

---

## 3) Domain Model (System Models)

> Tables below are the “minimum complete” set for the features described in the proposal: auth, wallet, subscription, invoicing, key mgmt, usage reports. fileciteturn0file0

### 3.1 Identity & Access
**users**
- `id`, `email`, `phone`, `name`, `password_hash` (optional), `status`, `created_at`…

**roles**, **permissions**, **role_user**, **permission_role** (or Laravel Gates/Policies)
- Admin / Developer / User (choose your granularity)

**otp_challenges**
- `id`, `user_id (nullable)`, `channel(email|sms)`, `destination`, `code_hash`, `expires_at`, `attempts`, `created_at`
- Purpose: login/register verification. fileciteturn0file0

**sessions** (optional; if using Sanctum tokens you may not need SQL sessions)
- With Redis-backed sessions: store session data in Redis

### 3.2 API Access
**api_clients**
- `id`, `user_id`, `name`, `status`

**api_keys**
- `id`, `api_client_id`, `key_prefix`, `key_hash`, `scopes(json)`, `rate_limit_per_min`, `allowed_ips(json)`, `expires_at`, `revoked_at`
- Store only hash + prefix; never store raw key.

**access_tokens** (if you also support bearer tokens; e.g., Laravel Sanctum)
- For dashboard sessions and management endpoints.

### 3.3 Providers & Models
**providers**
- `id`, `name`, `type`, `base_url`, `status`, `priority`, `config_encrypted(json)`

**provider_models**
- `id`, `provider_id`, `model_key`, `capabilities(json)`, `pricing_config(json)`, `status`

**routing_rules** (optional, but recommended)
- `id`, `match_scopes`, `strategy(round_robin|cheapest|fallback|fixed)`, `provider_model_id`…

### 3.4 Billing & Subscriptions
**wallets**
- `id`, `user_id`, `balance`, `currency`, `updated_at`

**wallet_transactions**
- `id`, `wallet_id`, `type(debit|credit|hold|release)`, `amount`, `reason`, `ref_type`, `ref_id`, `created_at`

**subscription_plans**
- `id`, `name`, `price`, `period(monthly|yearly)`, `included_credits`, `rate_limits(json)`, `features(json)`, `status`

**subscriptions**
- `id`, `user_id`, `plan_id`, `status(active|past_due|canceled)`, `starts_at`, `ends_at`, `renewal_at`, `provider_ref`

**invoices**
- `id`, `user_id`, `number`, `status(draft|issued|paid|void)`, `subtotal`, `tax`, `total`, `issued_at`, `paid_at`

**invoice_items**
- `id`, `invoice_id`, `type(subscription|usage|topup|adjustment)`, `description`, `quantity`, `unit_price`, `line_total`, `meta(json)`

### 3.5 Usage & Reporting
**gateway_requests**
- `id`, `api_key_id`, `user_id`, `provider_id`, `provider_model_id`, `endpoint`, `request_hash`, `status`, `latency_ms`, `created_at`

**usage_records**
- `id`, `gateway_request_id`, `metric(tokens_in|tokens_out|seconds|images)`, `quantity`, `unit_cost`, `total_cost`, `created_at`

**daily_usage_rollups** (optional for performance)
- Aggregations for dashboards and invoices

**audit_logs**
- `id`, `actor_user_id`, `action`, `target_type`, `target_id`, `meta(json)`, `created_at`

**provider_health_checks** (optional)
- Track uptime/latency; can be filled by scheduled jobs.

---

## 4) Service Layer (Modules & Specifications)

> Organize by “Domain” folders and keep controllers thin (request → service/pipeline → response).

### 4.1 Auth Module
**AuthService**
- Start OTP: `startOtp(destination, channel)`
- Verify OTP: `verifyOtp(destination, code)`
- Issue tokens: Sanctum (for dashboard), plus API key flows

**OTP Throttling**
- Redis counters per destination and per IP.

### 4.2 User/Account Module
**UserService**
- Profile read/update
- Security settings (2FA later), status management

### 4.3 API Key Module
**ApiKeyService**
- Create/revoke/list keys
- Rotate key
- Manage scopes & rate limits
- Store hashes, provide one-time “raw key” on creation only

### 4.4 Wallet & Billing Module
**WalletService**
- Credit/debit/hold/release
- Idempotency on payments
- Concurrency safe: SQL transactions + row locking strategy

**SubscriptionService**
- Create/cancel/renew subscriptions
- Plan enforcement (features, rate limits, included credits)

**InvoiceService**
- Generate invoice numbers
- Create invoice from subscription + usage
- Export PDF (queued job)

### 4.5 Gateway Module (Core)
**ProviderRegistry**
- Load enabled providers and models from DB/cache

**ProviderAdapterInterface**
- `supports(endpoint, modelKey)`
- `mapRequest(GatewayRequestDto): ProviderRequest`
- `send(ProviderRequest): ProviderResponse`
- `mapResponse(ProviderResponse): GatewayResponseDto`
- `extractUsage(ProviderResponse): UsageMetrics`

**ProviderRouter**
- Strategy-based selection (fixed/cheapest/fallback/round-robin)
- Uses `routing_rules`

**GatewayService**
- Entry point used by controllers
- Runs `GatewayRequestPipeline`

### 4.6 Usage & Reporting Module
**UsageMeteringService**
- Convert provider usage into normalized units
- Compute costs (per provider_model pricing_config)
- Emit events `UsageRecorded`

**ReportingService**
- Usage dashboards, exports (CSV), per key/user/provider, date filters
- Accountant-style reports from proposal: usage + invoices + wallet ledger. fileciteturn0file0

### 4.7 Infrastructure
**CacheService (Redis)**
- Provider/model cache
- Rate limit counters
- OTP throttling
- Feature flag cache (optional)

**Queue Jobs (Redis)**
- `SendOtpJob`
- `GenerateInvoicePdfJob`
- `AggregateDailyUsageJob`
- `ProviderHealthCheckJob`
- `PruneLogsJob`

---

## 5) API Specifications (v1)

### 5.1 Auth
- `POST /api/v1/auth/otp/start`
  - body: `{ channel, destination }`
- `POST /api/v1/auth/otp/verify`
  - body: `{ destination, code }`
  - returns: bearer token (Sanctum) for dashboard access

### 5.2 Users & Account
- `GET /api/v1/me`
- `PATCH /api/v1/me`

### 5.3 API Clients & Keys
- `POST /api/v1/api-clients`
- `GET /api/v1/api-clients`
- `POST /api/v1/api-clients/{id}/keys`
- `GET /api/v1/api-clients/{id}/keys`
- `POST /api/v1/api-keys/{id}/revoke`
- `POST /api/v1/api-keys/{id}/rotate`

### 5.4 Wallet & Billing
- `GET /api/v1/wallet`
- `POST /api/v1/wallet/topup` (if payment integrated)
- `GET /api/v1/wallet/transactions`

### 5.5 Subscriptions
- `GET /api/v1/plans`
- `POST /api/v1/subscriptions`
- `GET /api/v1/subscriptions/current`
- `POST /api/v1/subscriptions/cancel`

### 5.6 Invoices
- `GET /api/v1/invoices`
- `GET /api/v1/invoices/{id}`
- `GET /api/v1/invoices/{id}/pdf` (served from storage; generated async)

### 5.7 Gateway AI Endpoints (OpenAI-compatible style)
Pick a compatibility target for fastest adoption:
- `POST /api/v1/ai/chat/completions`
- `POST /api/v1/ai/embeddings`
- `POST /api/v1/ai/images/generations`
- `POST /api/v1/ai/audio/transcriptions`
- `POST /api/v1/ai/audio/speech`

Auth: `Authorization: Bearer <API_KEY>` (or `X-API-Key`)

Request options (common):
- `provider` (optional; if omitted, router selects)
- `model` (logical model name; mapped via routing_rules)
- `stream` boolean (later)

Response:
- Normalized to your chosen spec; include:
  - `provider`, `model`, `usage`, `request_id`, `data/result`

### 5.8 Reports
- `GET /api/v1/reports/usage?from=...&to=...&group_by=day|key|provider`
- `GET /api/v1/reports/wallet-ledger?from=...&to=...`
- `GET /api/v1/reports/invoices?status=...`

---

## 6) Cross-cutting Requirements

### 6.1 Security
- Hash API keys (bcrypt/argon2) and store only prefix + hash.
- Use signed URLs for invoice PDFs (expiry).
- Input validation: FormRequest + DTO mapping.
- Audit logs for sensitive operations (key create/revoke, plan change).
- Rate limiting: Redis-based, per key + per IP.
- Secrets: provider configs encrypted at rest (Laravel encrypted casts / custom encryption).

### 6.2 Observability
- Structured logs: request_id, api_key_id, provider, latency.
- Metrics: error rates per provider, queue depth, request latency.
- Tracing (optional): OpenTelemetry.

### 6.3 Reliability
- Provider fallback strategy (timeouts, retries with jitter).
- Circuit breaker (Redis counters + cooldown) optional.
- Idempotency keys for payment/top-up endpoints.

---

## 7) Implementation Plan (Step-by-step)

### Phase 0 — Repo & Environment (Day 1–2)
1. Create Laravel repo, configure PHP version, Pint, PHPStan.
2. Add Docker compose (or local) for:
   - SQL Server
   - Redis
3. Configure DB connection (`sqlsrv`), run a sample migration.
4. Configure queues to Redis (`QUEUE_CONNECTION=redis`), run a test job.

### Phase 1 — Core Foundation (Week 1)
1. Project structure:
   - `app/Domains/*` (Auth, Gateway, Billing, Reporting, Providers)
   - `app/Pipelines/*`
2. Base conventions:
   - DTOs, Actions/Services, Repositories (optional), Events
3. API versioning + exception handling + response envelope.
4. Auth for dashboard (Sanctum) + basic user model.

### Phase 2 — Auth via OTP (Week 2)
1. Implement `otp_challenges` table + OTP hashing.
2. OTP start/verify endpoints.
3. Redis throttling for OTP (by destination + IP).
4. Queue `SendOtpJob` (email first; SMS adapter stub). fileciteturn0file0

### Phase 3 — API Clients & Keys (Week 3)
1. Implement `api_clients`, `api_keys` tables.
2. Key creation (one-time raw key display), revoke, rotate.
3. Middleware: `AuthenticateApiKey` + scope checks.
4. Add audit logs for key operations.

### Phase 4 — Providers Abstraction + Gateway MVP (Week 4–5)
1. Implement `providers`, `provider_models`, `routing_rules`.
2. Provider adapter interface + first adapter (e.g., OpenAI-like).
3. Implement `GatewayRequestPipeline` end-to-end with logs.
4. Add `/ai/chat/completions` endpoint + normalization.
5. Store `gateway_requests` records and basic `usage_records`.

### Phase 5 — Wallet + Usage Billing (Week 6–7)
1. Implement wallet tables + transaction logic (atomic operations).
2. Enforce “wallet or subscription required” in pipeline.
3. Compute costs from `provider_models.pricing_config`.
4. Add rollups job (nightly) and usage reporting endpoint.

### Phase 6 — Subscriptions + Invoices (Week 8–9)
1. Implement plans/subscriptions tables.
2. Subscription enforcement (rate limits, included credits).
3. Invoice generation:
   - monthly invoice draft → issue → pay
4. Queue invoice PDF generation job, store file, serve with signed URL.

### Phase 7 — Reports, Admin, Hardening (Week 10–11)
1. Accountant/admin reports (usage, invoices, wallet ledger). fileciteturn0file0
2. Admin endpoints (or Filament/Nova if allowed).
3. Provider health checks + fallback tuning.
4. Data retention policies + pruning jobs.

### Phase 8 — Tests & Documentation (Week 12)
1. Unit tests for services + pipes.
2. Feature tests for key flows (OTP, key mgmt, gateway call, billing).
3. Load tests for gateway endpoints.
4. Publish OpenAPI (Swagger) + Postman collection. fileciteturn0file0

---

## 8) Deliverables Checklist
- [ ] SQL Server schema + migrations
- [ ] Redis cache + queue workers
- [ ] Auth via OTP (email/SMS)
- [ ] API client + API key management
- [ ] Provider abstraction + at least 2 providers
- [ ] Gateway endpoints (chat + at least one more capability)
- [ ] Wallet + usage metering + reporting
- [ ] Subscription plans + invoices + PDF export
- [ ] Automated tests + OpenAPI docs + Postman collection

---

## 9) Suggested Folder Structure (Laravel)
- `app/Domains/Auth/...`
- `app/Domains/Keys/...`
- `app/Domains/Gateway/...`
- `app/Domains/Billing/...`
- `app/Domains/Reporting/...`
- `app/Domains/Providers/...`
- `app/Pipelines/Gateway/...`
- `app/Http/Controllers/Api/V1/...`
- `app/Http/Middleware/...`
- `app/Jobs/...`
- `app/Events/...`
- `app/Listeners/...`

---

## 10) Notes for Codex Import
- Treat each **phase** as a milestone.
- Each bullet under phases becomes a Codex “task”.  
- Keep pipelines and services as the main implementation units (controllers stay thin).

