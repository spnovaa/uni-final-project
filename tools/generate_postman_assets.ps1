$ErrorActionPreference = 'Stop'

$projectRoot = Split-Path -Parent $PSScriptRoot
$outputDir = Join-Path $projectRoot 'docs\postman'
New-Item -ItemType Directory -Force -Path $outputDir | Out-Null

function New-Header($key, $value) {
    [ordered]@{
        key = $key
        value = $value
        type = 'text'
    }
}

function New-QueryParam($key, $value) {
    [ordered]@{
        key = $key
        value = $value
    }
}

function New-RawBody($raw) {
    [ordered]@{
        mode = 'raw'
        raw = $raw
        options = [ordered]@{
            raw = [ordered]@{
                language = 'json'
            }
        }
    }
}

function New-RawBodyFromObject($object) {
    $json = ($object | ConvertTo-Json -Depth 30) -replace "`r`n", "`n"
    New-RawBody $json
}

function New-FormDataBody($items) {
    [ordered]@{
        mode = 'formdata'
        formdata = $items
    }
}

function New-TextField($key, $value) {
    [ordered]@{
        key = $key
        value = $value
        type = 'text'
    }
}

function New-FileField($key, $src) {
    [ordered]@{
        key = $key
        src = $src
        type = 'file'
    }
}

function New-ApiUrl($segments, $query = $null) {
    $url = [ordered]@{
        raw = '{{baseUrl}}/' + ($segments -join '/')
        host = @('{{baseUrl}}')
        path = $segments
    }

    if ($query) {
        $url.query = $query
    }

    $url
}

function New-TestEvent($lines) {
    [ordered]@{
        listen = 'test'
        script = [ordered]@{
            type = 'text/javascript'
            exec = $lines
        }
    }
}

function New-RequestItem($name, $method, $url, $headers, $body, $description, $events = @()) {
    $request = [ordered]@{
        method = $method
        header = $headers
        url = $url
        description = $description
    }

    if ($null -ne $body) {
        $request.body = $body
    }

    $item = [ordered]@{
        name = $name
        request = $request
        response = @()
    }

    if ($events.Count -gt 0) {
        $item.event = $events
    }

    $item
}

function New-Folder($name, $items) {
    if ($items.Count -eq 1 -and $items[0] -is [System.Array]) {
        $items = $items[0]
    }

    [ordered]@{
        name = $name
        item = $items
    }
}

function Normalize-CollectionItems($items) {
    foreach ($item in $items) {
        if ($item.item) {
            if ($item.item.Count -eq 1 -and $item.item[0] -is [System.Array]) {
                $item.item = $item.item[0]
            }

            Normalize-CollectionItems $item.item
        }
    }
}

$okEvent = New-TestEvent @(
    "pm.test('Request completed', function () {",
    "  pm.expect(pm.response.code).to.be.oneOf([200, 201, 202]);",
    "});"
)

$saveSanctumToken = New-TestEvent @(
    "pm.response.to.have.status(200);",
    "const data = pm.response.json();",
    "if (data.token) pm.environment.set('sanctumToken', data.token);",
    "if (data.user && data.user.id) pm.environment.set('userId', String(data.user.id));"
)

$saveApiClient = New-TestEvent @(
    "const data = pm.response.json();",
    "if (data.id) pm.environment.set('apiClientId', String(data.id));"
)

$saveApiKey = New-TestEvent @(
    "const data = pm.response.json();",
    "if (data.api_key) pm.environment.set('apiKey', data.api_key);",
    "if (data.key_prefix) pm.environment.set('apiKeyPrefix', data.key_prefix);",
    "if (data.resource && data.resource.id) pm.environment.set('apiKeyId', String(data.resource.id));"
)

$savePlan = New-TestEvent @(
    "const data = pm.response.json();",
    "if (data.id) pm.environment.set('planId', String(data.id));"
)

$saveInvoiceFromList = New-TestEvent @(
    "const data = pm.response.json();",
    "if (Array.isArray(data) && data.length > 0) {",
    "  if (data[0].id) pm.environment.set('invoiceId', String(data[0].id));",
    "  if (data[0].pdf_url) pm.environment.set('signedInvoicePdfUrl', data[0].pdf_url);",
    "}"
)

$saveInvoiceDetail = New-TestEvent @(
    "const data = pm.response.json();",
    "if (data.id) pm.environment.set('invoiceId', String(data.id));",
    "if (data.pdf_url) pm.environment.set('signedInvoicePdfUrl', data.pdf_url);"
)

$saveProvider = New-TestEvent @(
    "const data = pm.response.json();",
    "if (data.id) pm.environment.set('providerId', String(data.id));",
    "if (data.name) pm.environment.set('providerName', data.name);"
)

$saveProviderModel = New-TestEvent @(
    "const data = pm.response.json();",
    "if (data.id) pm.environment.set('providerModelId', String(data.id));",
    "if (data.model_key) pm.environment.set('providerModelKey', data.model_key);"
)

$acceptJsonHeaders = @(
    (New-Header 'Accept' 'application/json')
)
$jsonHeaders = @(
    (New-Header 'Accept' 'application/json'),
    (New-Header 'Content-Type' 'application/json')
)
$sanctumHeaders = @(
    (New-Header 'Accept' 'application/json'),
    (New-Header 'Authorization' 'Bearer {{sanctumToken}}')
)
$sanctumJsonHeaders = @(
    (New-Header 'Accept' 'application/json'),
    (New-Header 'Content-Type' 'application/json'),
    (New-Header 'Authorization' 'Bearer {{sanctumToken}}')
)
$apiKeyHeaders = @(
    (New-Header 'Accept' 'application/json'),
    (New-Header 'Content-Type' 'application/json'),
    (New-Header 'Authorization' 'Bearer {{apiKey}}')
)
$xApiKeyHeaders = @(
    (New-Header 'Accept' 'application/json'),
    (New-Header 'Content-Type' 'application/json'),
    (New-Header 'X-API-Key' '{{apiKey}}')
)
$apiKeyFormHeaders = @(
    (New-Header 'Accept' 'application/json'),
    (New-Header 'Authorization' 'Bearer {{apiKey}}')
)

$otpStartBody = New-RawBodyFromObject ([ordered]@{
    channel = '{{otpChannel}}'
    destination = '{{otpDestination}}'
})

$otpVerifyBody = New-RawBodyFromObject ([ordered]@{
    destination = '{{otpDestination}}'
    code = '{{otpCode}}'
    channel = '{{otpChannel}}'
})

$meUpdateBody = New-RawBodyFromObject ([ordered]@{
    name = 'Updated User'
    phone = '09120000000'
})

$apiClientBody = New-RawBodyFromObject ([ordered]@{
    name = 'Primary Client'
})

$apiKeyCreateBody = New-RawBodyFromObject ([ordered]@{
    scopes = @('ai:chat', 'ai:embeddings')
    rate_limit_per_min = 60
    allowed_ips = @('127.0.0.1')
})

$walletTopupBody = New-RawBodyFromObject ([ordered]@{
    amount = 25.50
    reason = 'postman_demo_topup'
})

$planCreateBody = New-RawBodyFromObject ([ordered]@{
    name = 'Starter'
    price = 9.99
    currency = 'USD'
    period = 'monthly'
    included_credits = 100
    status = 'active'
})

$subscriptionCreateBody = New-RawBodyFromObject ([ordered]@{
    plan_id = '{{planId}}'
})

$providerCreateBody = New-RawBodyFromObject ([ordered]@{
    name = 'openrouter'
    type = 'openai_compatible'
    base_url = 'https://openrouter.ai/api/v1'
    api_key = 'replace-with-real-key-if-needed'
    status = 'active'
})

$providerModelBody = New-RawBodyFromObject ([ordered]@{
    model_key = 'gpt-4o-mini'
    capabilities = [ordered]@{
        chat = $true
    }
    pricing_config = [ordered]@{
        input_token = 0.0005
        output_token = 0.001
    }
})

$responsesBody = New-RawBodyFromObject ([ordered]@{
    provider = '{{aiProvider}}'
    model = '{{aiModel}}'
    input = 'Hello'
})

$chatBody = New-RawBodyFromObject ([ordered]@{
    provider = '{{aiProvider}}'
    model = '{{aiModel}}'
    messages = @(
        [ordered]@{
            role = 'user'
            content = 'Hello!'
        }
    )
})

$embeddingsBody = New-RawBodyFromObject ([ordered]@{
    provider = '{{aiProvider}}'
    model = 'text-embedding-3-small'
    input = 'hello'
})

$imageBody = New-RawBodyFromObject ([ordered]@{
    provider = '{{aiProvider}}'
    model = 'gpt-image-1'
    prompt = 'A test image'
})

$audioSpeechBody = New-RawBodyFromObject ([ordered]@{
    provider = '{{aiProvider}}'
    model = 'gpt-4o-mini-tts'
    input = 'Hello there'
    voice = 'alloy'
})

$fullCollectionItems = @(
    (New-Folder '1. Authentication' @(
        (New-RequestItem 'Start OTP' 'POST' (New-ApiUrl @('api','v1','auth','otp','start')) $jsonHeaders $otpStartBody 'Start an OTP challenge for the demo user.' @($okEvent)),
        (New-RequestItem 'Verify OTP' 'POST' (New-ApiUrl @('api','v1','auth','otp','verify')) $jsonHeaders $otpVerifyBody 'Verify the OTP and store the returned Sanctum token in the environment.' @($saveSanctumToken))
    )),
    (New-Folder '2. Profile' @(
        (New-RequestItem 'Get Me' 'GET' (New-ApiUrl @('api','v1','me')) $sanctumHeaders $null 'Fetch the authenticated user profile.' @($okEvent)),
        (New-RequestItem 'Update Me (JSON)' 'PATCH' (New-ApiUrl @('api','v1','me')) $sanctumJsonHeaders $meUpdateBody 'Update the authenticated user profile with a JSON payload.' @($okEvent)),
        (New-RequestItem 'Update Me (Profile Image)' 'PATCH' (New-ApiUrl @('api','v1','me')) $sanctumHeaders (New-FormDataBody @(
            (New-TextField 'name' 'Updated User With Avatar'),
            (New-FileField 'profile_image' '{{profileImagePath}}')
        )) 'Update the authenticated user profile image. Select a local image file if Postman does not resolve the placeholder path automatically.' @($okEvent))
    )),
    (New-Folder '3. API Clients & Keys' @(
        (New-RequestItem 'List API Clients' 'GET' (New-ApiUrl @('api','v1','api-clients')) $sanctumHeaders $null 'List the current user''s API clients.' @($okEvent)),
        (New-RequestItem 'Create API Client' 'POST' (New-ApiUrl @('api','v1','api-clients')) $sanctumJsonHeaders $apiClientBody 'Create a new API client and store its id.' @($saveApiClient)),
        (New-RequestItem 'List API Keys' 'GET' (New-ApiUrl @('api','v1','api-clients','{{apiClientId}}','keys')) $sanctumHeaders $null 'List API keys for the selected API client.' @($okEvent)),
        (New-RequestItem 'Create API Key' 'POST' (New-ApiUrl @('api','v1','api-clients','{{apiClientId}}','keys')) $sanctumJsonHeaders $apiKeyCreateBody 'Create a new API key. The plaintext key is stored in the environment as apiKey.' @($saveApiKey)),
        (New-RequestItem 'Revoke API Key' 'POST' (New-ApiUrl @('api','v1','api-keys','{{apiKeyId}}','revoke')) $sanctumHeaders $null 'Revoke the selected API key.' @($okEvent)),
        (New-RequestItem 'Rotate API Key' 'POST' (New-ApiUrl @('api','v1','api-keys','{{apiKeyId}}','rotate')) $sanctumHeaders $null 'Rotate the selected API key and update the stored apiKey variable.' @($saveApiKey))
    )),
    (New-Folder '4. Wallet & Plans' @(
        (New-RequestItem 'Get Wallet' 'GET' (New-ApiUrl @('api','v1','wallet')) $sanctumHeaders $null 'Get the current wallet balance.' @($okEvent)),
        (New-RequestItem 'Topup Wallet' 'POST' (New-ApiUrl @('api','v1','wallet','topup')) $sanctumJsonHeaders $walletTopupBody 'Increase the wallet balance so subscription and AI flows can be exercised.' @($okEvent)),
        (New-RequestItem 'List Wallet Transactions' 'GET' (New-ApiUrl @('api','v1','wallet','transactions') @((New-QueryParam 'limit' '10'))) $sanctumHeaders $null 'List wallet transactions.' @($okEvent)),
        (New-RequestItem 'List Plans' 'GET' (New-ApiUrl @('api','v1','plans')) $sanctumHeaders $null 'List subscription plans.' @($okEvent)),
        (New-RequestItem 'Create Plan' 'POST' (New-ApiUrl @('api','v1','plans')) $sanctumJsonHeaders $planCreateBody 'Create a sample subscription plan and store its id.' @($savePlan))
    )),
    (New-Folder '5. Subscriptions & Invoices' @(
        (New-RequestItem 'Create Subscription' 'POST' (New-ApiUrl @('api','v1','subscriptions')) $sanctumJsonHeaders $subscriptionCreateBody 'Create a subscription for the selected plan.' @($okEvent)),
        (New-RequestItem 'Get Current Subscription' 'GET' (New-ApiUrl @('api','v1','subscriptions','current')) $sanctumHeaders $null 'Fetch the current active subscription.' @($okEvent)),
        (New-RequestItem 'Cancel Subscription' 'POST' (New-ApiUrl @('api','v1','subscriptions','cancel')) $sanctumHeaders $null 'Cancel the current subscription.' @($okEvent)),
        (New-RequestItem 'List Invoices' 'GET' (New-ApiUrl @('api','v1','invoices') @((New-QueryParam 'limit' '10'))) $sanctumHeaders $null 'List invoices and store the first invoice id and signed pdf URL when available.' @($saveInvoiceFromList)),
        (New-RequestItem 'Get Invoice Detail' 'GET' (New-ApiUrl @('api','v1','invoices','{{invoiceId}}')) $sanctumHeaders $null 'Fetch invoice details and store its signed PDF URL.' @($saveInvoiceDetail)),
        (New-RequestItem 'Download Invoice PDF (Signed URL)' 'GET' '{{signedInvoicePdfUrl}}' $acceptJsonHeaders $null 'Use the signed PDF URL returned by invoice detail.' @($okEvent))
    )),
    (New-Folder '6. Reports' @(
        (New-RequestItem 'Usage Report' 'GET' (New-ApiUrl @('api','v1','reports','usage') @((New-QueryParam 'group_by' 'day'))) $sanctumHeaders $null 'Fetch grouped usage metrics.' @($okEvent)),
        (New-RequestItem 'Wallet Ledger Report' 'GET' (New-ApiUrl @('api','v1','reports','wallet-ledger')) $sanctumHeaders $null 'Fetch wallet ledger entries.' @($okEvent)),
        (New-RequestItem 'Invoice Report' 'GET' (New-ApiUrl @('api','v1','reports','invoices') @((New-QueryParam 'status' 'issued'))) $sanctumHeaders $null 'Fetch invoice report entries filtered by status.' @($okEvent))
    )),
    (New-Folder '7. Providers & Models' @(
        (New-RequestItem 'List Providers' 'GET' (New-ApiUrl @('api','v1','providers')) $sanctumHeaders $null 'List provider records.' @($okEvent)),
        (New-RequestItem 'Create Provider' 'POST' (New-ApiUrl @('api','v1','providers')) $sanctumJsonHeaders $providerCreateBody 'Create a provider record and store its id.' @($saveProvider)),
        (New-RequestItem 'List Provider Models' 'GET' (New-ApiUrl @('api','v1','providers','{{providerId}}','models')) $sanctumHeaders $null 'List models for the selected provider.' @($okEvent)),
        (New-RequestItem 'Create Provider Model' 'POST' (New-ApiUrl @('api','v1','providers','{{providerId}}','models')) $sanctumJsonHeaders $providerModelBody 'Create a provider model mapping and store its id.' @($saveProviderModel))
    )),
    (New-Folder '8. AI Gateway' @(
        (New-RequestItem 'Responses API' 'POST' (New-ApiUrl @('api','v1','ai','responses')) $apiKeyHeaders $responsesBody 'OpenAI-compatible responses endpoint authenticated with Authorization Bearer apiKey.' @($okEvent)),
        (New-RequestItem 'Chat Completions' 'POST' (New-ApiUrl @('api','v1','ai','chat','completions')) $apiKeyHeaders $chatBody 'OpenAI-compatible chat completions endpoint.' @($okEvent)),
        (New-RequestItem 'Embeddings (X-API-Key)' 'POST' (New-ApiUrl @('api','v1','ai','embeddings')) $xApiKeyHeaders $embeddingsBody 'Embeddings endpoint authenticated with X-API-Key.' @($okEvent)),
        (New-RequestItem 'Image Generation' 'POST' (New-ApiUrl @('api','v1','ai','images','generations')) $apiKeyHeaders $imageBody 'Image generation endpoint.' @($okEvent)),
        (New-RequestItem 'Audio Transcriptions' 'POST' (New-ApiUrl @('api','v1','ai','audio','transcriptions')) $apiKeyFormHeaders (New-FormDataBody @(
            (New-TextField 'provider' '{{aiProvider}}'),
            (New-TextField 'model' 'whisper-1'),
            (New-FileField 'file' '{{audioFilePath}}')
        )) 'Audio transcription endpoint. Select a local audio file if Postman does not resolve the placeholder path automatically.' @($okEvent)),
        (New-RequestItem 'Audio Speech' 'POST' (New-ApiUrl @('api','v1','ai','audio','speech')) $apiKeyHeaders $audioSpeechBody 'Text-to-speech endpoint. A binary audio response is expected.' @($okEvent))
    )),
    (New-Folder '9. Audit Logs (Admin)' @(
        (New-RequestItem 'List Audit Logs' 'GET' (New-ApiUrl @('api','v1','audit-logs') @((New-QueryParam 'limit' '20'))) $sanctumHeaders $null 'Requires an authenticated user with the admin role.' @($okEvent))
    ))
)

$screenshotFlowItems = @(
    (New-Folder 'Screenshot Flow' @(
        (New-RequestItem '01 - Start OTP' 'POST' (New-ApiUrl @('api','v1','auth','otp','start')) $jsonHeaders $otpStartBody 'Start the OTP flow.' @($okEvent)),
        (New-RequestItem '02 - Verify OTP' 'POST' (New-ApiUrl @('api','v1','auth','otp','verify')) $jsonHeaders $otpVerifyBody 'Verify OTP and store the Sanctum token.' @($saveSanctumToken)),
        (New-RequestItem '03 - Get Me' 'GET' (New-ApiUrl @('api','v1','me')) $sanctumHeaders $null 'Capture the authenticated profile response.' @($okEvent)),
        (New-RequestItem '04 - Create API Client' 'POST' (New-ApiUrl @('api','v1','api-clients')) $sanctumJsonHeaders $apiClientBody 'Create a client for subsequent API-key screenshots.' @($saveApiClient)),
        (New-RequestItem '05 - Create API Key' 'POST' (New-ApiUrl @('api','v1','api-clients','{{apiClientId}}','keys')) $sanctumJsonHeaders (New-RawBodyFromObject ([ordered]@{
            scopes = @('ai:chat', 'ai:embeddings')
            rate_limit_per_min = 60
        })) 'Create an API key and store it for gateway requests.' @($saveApiKey)),
        (New-RequestItem '06 - Topup Wallet' 'POST' (New-ApiUrl @('api','v1','wallet','topup')) $sanctumJsonHeaders $walletTopupBody 'Fund the wallet so billing-related flows are visible.' @($okEvent)),
        (New-RequestItem '07 - Create Plan' 'POST' (New-ApiUrl @('api','v1','plans')) $sanctumJsonHeaders $planCreateBody 'Create a plan for subscription screenshots.' @($savePlan)),
        (New-RequestItem '08 - Create Subscription' 'POST' (New-ApiUrl @('api','v1','subscriptions')) $sanctumJsonHeaders $subscriptionCreateBody 'Create a subscription.' @($okEvent)),
        (New-RequestItem '09 - Create Provider' 'POST' (New-ApiUrl @('api','v1','providers')) $sanctumJsonHeaders $providerCreateBody 'Create a provider record for administrative screenshots.' @($saveProvider)),
        (New-RequestItem '10 - Create Provider Model' 'POST' (New-ApiUrl @('api','v1','providers','{{providerId}}','models')) $sanctumJsonHeaders $providerModelBody 'Create a provider model mapping.' @($saveProviderModel)),
        (New-RequestItem '11 - Chat Completions' 'POST' (New-ApiUrl @('api','v1','ai','chat','completions')) $apiKeyHeaders $chatBody 'Capture a successful gateway request if upstream keys are configured.' @($okEvent)),
        (New-RequestItem '12 - Usage Report' 'GET' (New-ApiUrl @('api','v1','reports','usage') @((New-QueryParam 'group_by' 'day'))) $sanctumHeaders $null 'Capture reporting output.' @($okEvent)),
        (New-RequestItem '13 - List Invoices' 'GET' (New-ApiUrl @('api','v1','invoices') @((New-QueryParam 'limit' '10'))) $sanctumHeaders $null 'Capture billing/invoice output.' @($saveInvoiceFromList))
    ))
)

$fullCollection = [ordered]@{
    info = [ordered]@{
        _postman_id = [guid]::NewGuid().Guid
        name = 'Unified AI Gateway - Full API'
        schema = 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
        description = 'Comprehensive Postman collection for the Unified AI Gateway thesis project.'
    }
    item = $fullCollectionItems
    variable = @(
        [ordered]@{
            key = 'baseUrl'
            value = 'http://127.0.0.1:8000'
        }
    )
}

$screenshotCollection = [ordered]@{
    info = [ordered]@{
        _postman_id = [guid]::NewGuid().Guid
        name = 'Unified AI Gateway - Screenshot Flow'
        schema = 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
        description = 'Curated request flow for collecting execution screenshots for the thesis.'
    }
    item = $screenshotFlowItems
    variable = @(
        [ordered]@{
            key = 'baseUrl'
            value = 'http://127.0.0.1:8000'
        }
    )
}

if ($fullCollectionItems.Count -eq 1 -and $fullCollectionItems[0] -is [System.Array]) {
    $fullCollectionItems = $fullCollectionItems[0]
}

Normalize-CollectionItems $fullCollectionItems
Normalize-CollectionItems $screenshotFlowItems

$environment = [ordered]@{
    id = [guid]::NewGuid().Guid
    name = 'Unified AI Gateway - Local'
    values = @(
        [ordered]@{ key = 'baseUrl'; value = 'http://127.0.0.1:8000'; type = 'default'; enabled = $true },
        [ordered]@{ key = 'otpChannel'; value = 'email'; type = 'default'; enabled = $true },
        [ordered]@{ key = 'otpDestination'; value = 'user@example.com'; type = 'default'; enabled = $true },
        [ordered]@{ key = 'otpCode'; value = '123456'; type = 'default'; enabled = $true },
        [ordered]@{ key = 'sanctumToken'; value = ''; type = 'secret'; enabled = $true },
        [ordered]@{ key = 'userId'; value = ''; type = 'default'; enabled = $true },
        [ordered]@{ key = 'apiClientId'; value = ''; type = 'default'; enabled = $true },
        [ordered]@{ key = 'apiKey'; value = ''; type = 'secret'; enabled = $true },
        [ordered]@{ key = 'apiKeyId'; value = ''; type = 'default'; enabled = $true },
        [ordered]@{ key = 'apiKeyPrefix'; value = ''; type = 'default'; enabled = $true },
        [ordered]@{ key = 'planId'; value = ''; type = 'default'; enabled = $true },
        [ordered]@{ key = 'invoiceId'; value = ''; type = 'default'; enabled = $true },
        [ordered]@{ key = 'signedInvoicePdfUrl'; value = ''; type = 'default'; enabled = $true },
        [ordered]@{ key = 'providerId'; value = ''; type = 'default'; enabled = $true },
        [ordered]@{ key = 'providerName'; value = 'openai'; type = 'default'; enabled = $true },
        [ordered]@{ key = 'providerModelId'; value = ''; type = 'default'; enabled = $true },
        [ordered]@{ key = 'providerModelKey'; value = 'gpt-4o-mini'; type = 'default'; enabled = $true },
        [ordered]@{ key = 'aiProvider'; value = 'openai'; type = 'default'; enabled = $true },
        [ordered]@{ key = 'aiModel'; value = 'gpt-4o-mini'; type = 'default'; enabled = $true },
        [ordered]@{ key = 'profileImagePath'; value = 'C:\\path\\to\\avatar.jpg'; type = 'default'; enabled = $true },
        [ordered]@{ key = 'audioFilePath'; value = 'C:\\path\\to\\sample.wav'; type = 'default'; enabled = $true }
    )
    _postman_variable_scope = 'environment'
    _postman_exported_at = (Get-Date).ToString('o')
    _postman_exported_using = 'OpenAI Codex'
}

$readme = @'
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
'@

$fullCollectionPath = Join-Path $outputDir 'Unified AI Gateway - Full API.postman_collection.json'
$screenshotCollectionPath = Join-Path $outputDir 'Unified AI Gateway - Screenshot Flow.postman_collection.json'
$environmentPath = Join-Path $outputDir 'Unified AI Gateway - Local.postman_environment.json'
$readmePath = Join-Path $outputDir 'README.md'

$fullCollection | ConvertTo-Json -Depth 100 | Set-Content -Path $fullCollectionPath -Encoding UTF8
$screenshotCollection | ConvertTo-Json -Depth 100 | Set-Content -Path $screenshotCollectionPath -Encoding UTF8
$environment | ConvertTo-Json -Depth 100 | Set-Content -Path $environmentPath -Encoding UTF8
$readme | Set-Content -Path $readmePath -Encoding UTF8

Get-Content $fullCollectionPath -Raw | ConvertFrom-Json | Out-Null
Get-Content $screenshotCollectionPath -Raw | ConvertFrom-Json | Out-Null
Get-Content $environmentPath -Raw | ConvertFrom-Json | Out-Null

Write-Output 'postman=ok'
