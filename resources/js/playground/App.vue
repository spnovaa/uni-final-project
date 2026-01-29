<template>
    <div class="page">
        <div class="card">
            <header class="header">
                <h1>AI Gateway Playground</h1>
                <p>Test the OpenAI-compatible endpoints with your API key.</p>
            </header>

            <div class="grid">
                <div class="field">
                    <label for="endpoint">Endpoint</label>
                    <select id="endpoint" v-model="endpoint">
                        <option value="/api/v1/ai/chat/completions">/api/v1/ai/chat/completions</option>
                        <option value="/api/v1/ai/responses">/api/v1/ai/responses</option>
                        <option value="/api/v1/ai/embeddings">/api/v1/ai/embeddings</option>
                        <option value="/api/v1/ai/images/generations">/api/v1/ai/images/generations</option>
                        <option value="/api/v1/ai/audio/transcriptions">/api/v1/ai/audio/transcriptions</option>
                        <option value="/api/v1/ai/audio/speech">/api/v1/ai/audio/speech</option>
                    </select>
                </div>
                <div class="field">
                    <label for="provider">Provider</label>
                    <select id="provider" v-model="provider">
                        <option value="openai">openai</option>
                        <option value="gemini">gemini</option>
                    </select>
                </div>
                <div class="field">
                    <label for="model">Model</label>
                    <input id="model" v-model="model" type="text" placeholder="gpt-4o-mini">
                </div>
            </div>

            <div class="field">
                <label for="apiKey">API Key</label>
                <input id="apiKey" v-model="apiKey" type="text" placeholder="gk_...">
                <small>Your key is sent using the Authorization header.</small>
            </div>

            <div class="field">
                <label for="payload">Payload (JSON)</label>
                <textarea id="payload" v-model="payload"></textarea>
            </div>

            <button :disabled="loading" @click="sendRequest">
                {{ loading ? 'Sending...' : 'Send Request' }}
            </button>

            <div class="field">
                <label>Response</label>
                <pre>{{ responseText }}</pre>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const endpoint = ref('/api/v1/ai/chat/completions');
const provider = ref('openai');
const model = ref('gpt-4o-mini');
const apiKey = ref('');
const payload = ref('{"messages":[{"role":"user","content":"Hello"}]}');
const responseText = ref('Waiting for request...');
const loading = ref(false);

const mergeSelection = (data) => {
    let updated = { ...data };
    if (!updated.provider && provider.value) {
        updated = { ...updated, provider: provider.value };
    }
    if (!updated.model && model.value) {
        updated = { ...updated, model: model.value };
    }
    return updated;
};

const sendRequest = async () => {
    responseText.value = 'Sending...';
    loading.value = true;

    try {
        const key = apiKey.value.trim();
        if (!key) {
            responseText.value = 'API key is required.';
            return;
        }

        const parsed = JSON.parse(payload.value);
        const body = mergeSelection(parsed);

        const res = await fetch(endpoint.value, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${key}`,
            },
            body: JSON.stringify(body),
        });

        const text = await res.text();
        try {
            responseText.value = JSON.stringify(JSON.parse(text), null, 2);
        } catch (err) {
            responseText.value = text;
        }
    } catch (err) {
        responseText.value = err?.message ?? String(err);
    } finally {
        loading.value = false;
    }
};
</script>

<style scoped>
.page {
    min-height: 100vh;
    background: linear-gradient(135deg, #f8fafc, #e2e8f0);
    padding: 32px 16px;
    color: #0f172a;
}

.card {
    max-width: 980px;
    margin: 0 auto;
    background: #ffffff;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1);
}

.header {
    margin-bottom: 16px;
}

h1 {
    margin: 0 0 8px;
    font-size: 26px;
}

p {
    margin: 0;
    color: #475569;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 16px;
}

.field {
    margin-top: 16px;
    display: flex;
    flex-direction: column;
}

label {
    font-weight: 600;
    margin-bottom: 8px;
}

input,
select,
textarea {
    padding: 10px 12px;
    border: 1px solid #cbd5f5;
    border-radius: 10px;
    font-size: 14px;
}

textarea {
    min-height: 160px;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

small {
    margin-top: 6px;
    color: #64748b;
}

button {
    margin-top: 16px;
    padding: 12px 18px;
    border: none;
    border-radius: 10px;
    background: #0f172a;
    color: #fff;
    font-weight: 600;
    cursor: pointer;
}

button:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

pre {
    background: #0b1021;
    color: #e2e8f0;
    padding: 16px;
    border-radius: 10px;
    overflow-x: auto;
}
</style>
