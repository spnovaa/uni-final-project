<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Gateway Playground</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 24px; background: #f8fafc; color: #0f172a; }
        .card { max-width: 980px; margin: 0 auto; background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08); }
        h1 { margin-top: 0; font-size: 24px; }
        label { display: block; font-weight: 600; margin-top: 16px; }
        input, select, textarea { width: 100%; padding: 10px 12px; margin-top: 8px; border: 1px solid #cbd5f5; border-radius: 8px; font-size: 14px; }
        textarea { min-height: 160px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        button { margin-top: 16px; padding: 12px 16px; border: none; background: #0f172a; color: white; border-radius: 8px; font-weight: 600; cursor: pointer; }
        button:disabled { opacity: 0.6; cursor: not-allowed; }
        pre { background: #0b1021; color: #e2e8f0; padding: 16px; border-radius: 8px; overflow-x: auto; }
        .hint { font-size: 12px; color: #475569; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="card">
        <h1>AI Gateway Playground</h1>
        <div class="row">
            <div>
                <label for="endpoint">Endpoint</label>
                <select id="endpoint">
                    <option value="/api/v1/ai/chat/completions">/api/v1/ai/chat/completions</option>
                    <option value="/api/v1/ai/responses">/api/v1/ai/responses</option>
                    <option value="/api/v1/ai/embeddings">/api/v1/ai/embeddings</option>
                </select>
            </div>
            <div>
                <label for="model">Model</label>
                <input id="model" type="text" value="gpt-4o-mini">
            </div>
        </div>

        <label for="apiKey">API Key</label>
        <input id="apiKey" type="text" placeholder="gk_...">
        <div class="hint">Your API key is required. It will be sent in the Authorization header.</div>

        <label for="payload">Payload (JSON)</label>
        <textarea id="payload">{"messages":[{"role":"user","content":"Hello"}]}</textarea>

        <button id="sendBtn">Send Request</button>

        <label>Response</label>
        <pre id="response">Waiting for request...</pre>
    </div>

    <script>
        const endpointInput = document.getElementById('endpoint');
        const modelInput = document.getElementById('model');
        const apiKeyInput = document.getElementById('apiKey');
        const payloadInput = document.getElementById('payload');
        const responseBlock = document.getElementById('response');
        const sendBtn = document.getElementById('sendBtn');

        function mergeModel(payload, model) {
            const data = { ...payload };
            if (!data.model) {
                data.model = model;
            }
            return data;
        }

        sendBtn.addEventListener('click', async () => {
            responseBlock.textContent = 'Sending...';
            sendBtn.disabled = true;

            try {
                const apiKey = apiKeyInput.value.trim();
                if (!apiKey) {
                    responseBlock.textContent = 'API key is required.';
                    sendBtn.disabled = false;
                    return;
                }

                const payload = JSON.parse(payloadInput.value);
                const endpoint = endpointInput.value;
                const data = mergeModel(payload, modelInput.value.trim());

                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': 'Bearer ' + apiKey
                    },
                    body: JSON.stringify(data)
                });

                const text = await res.text();
                try {
                    responseBlock.textContent = JSON.stringify(JSON.parse(text), null, 2);
                } catch (err) {
                    responseBlock.textContent = text;
                }
            } catch (err) {
                responseBlock.textContent = err.message || String(err);
            } finally {
                sendBtn.disabled = false;
            }
        });
    </script>
</body>
</html>
