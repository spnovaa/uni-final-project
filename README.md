# Unified AI Gateway

Unified AI Gateway is a Laravel-based backend for routing AI requests through a single OpenAI-compatible API while handling authentication, API key management, billing, subscriptions, invoicing, reporting, and provider administration.

This repository contains both the application code and the thesis materials used to document the design and implementation.

## Core Capabilities

- OpenAI-compatible AI endpoints for chat completions, responses, embeddings, image generation, speech, and transcription
- Multi-provider routing across OpenAI, Gemini, Groq, and OpenRouter
- OTP-based user authentication with Sanctum for dashboard access
- Project-level API clients and API keys with rotation, revocation, rate limits, and IP allowlists
- Wallet, subscription, invoice, and usage-reporting modules
- Admin endpoints for provider/model management and audit logs
- Vue-based playground for exercising gateway endpoints interactively

## Tech Stack

- Backend: PHP 8.2, Laravel 12
- Frontend: Vue 3, Vite
- Database: SQL Server
- Cache and queue: Redis
- PDF generation: Dompdf
- API documentation support: L5 Swagger

## Repository Layout

- `app/` application services, domains, pipelines, controllers, models, and resources
- `routes/` HTTP route definitions
- `resources/js/` Vue playground application
- `tests/` unit and feature tests
- `docs/` exported or supporting project documentation
- `thesis/` LaTeX thesis source and generated PDF

## Prerequisites

- PHP 8.2+
- Composer
- Node.js and npm
- SQL Server
- Redis

## Local Setup

1. Install PHP dependencies:

```bash
composer install
```

2. Install frontend dependencies:

```bash
npm install
```

3. Create the local environment file:

```bash
copy .env.example .env
```

4. Generate the Laravel application key:

```bash
php artisan key:generate
```

5. Update `.env` with your SQL Server and Redis settings.

6. Create the development database referenced by `.env`.

7. Run migrations:

```bash
php artisan migrate
```

8. Build frontend assets:

```bash
npm run build
```

## Development

Run the full local development stack:

```bash
composer run dev
```

This starts:

- the Laravel HTTP server
- the queue worker
- Laravel Pail for logs
- the Vite development server

## Running Tests

Tests are expected to run against the testing environment defined in `.env.testing`.

Before running the suite:

1. Create the testing database referenced by `.env.testing`
2. Ensure the SQL Server credentials in `.env.testing` are valid
3. Run migrations for the testing database

Example:

```bash
php artisan migrate --env=testing
php artisan test
```

If you want a clean test database before execution:

```bash
php artisan migrate:fresh --env=testing
php artisan test
```

## API Surface

The main versioned routes are defined under `routes/api.php` and include:

- `POST /api/v1/auth/otp/start`
- `POST /api/v1/auth/otp/verify`
- `GET/PATCH /api/v1/me`
- API client and API key management routes
- wallet, plan, subscription, invoice, and reporting routes
- provider and provider-model administration routes
- OpenAI-compatible AI routes under `/api/v1/ai/*`

Detailed endpoint documentation and request/response samples are included in the thesis appendix at:

- `thesis/appendix1.tex`
- `thesis/AUTthesis.pdf`

## Thesis

The thesis source is located in `thesis/`.

Main entry file:

```bash
thesis/AUTthesis.tex
```

Compiled output:

```bash
thesis/AUTthesis.pdf
```

## Notes

- This project depends on SQL Server, so running the application or tests without creating the target databases first will fail.
- Some operational integrations, such as external OTP delivery and payment-gateway connectivity, are intentionally represented in a development-friendly form and are discussed in the thesis as current limitations or future work.
