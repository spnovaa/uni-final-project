<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice->number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        .meta { margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border-bottom: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f5f5f5; }
        .totals { margin-top: 16px; }
        .totals td { border: none; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>Invoice {{ $invoice->number }}</h1>
    <div class="meta">
        <div>Status: {{ strtoupper($invoice->status) }}</div>
        <div>Issued: {{ optional($invoice->issued_at)->format('Y-m-d') }}</div>
        <div>Currency: {{ $invoice->currency }}</div>
        <div>Customer ID: {{ $invoice->user_id }}</div>
    </div>

    <table>
        <thead>
        <tr>
            <th>Description</th>
            <th>Type</th>
            <th class="right">Quantity</th>
            <th class="right">Unit Price</th>
            <th class="right">Line Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td>{{ $item->type }}</td>
                <td class="right">{{ $item->quantity }}</td>
                <td class="right">{{ number_format((float) $item->unit_price, 4) }}</td>
                <td class="right">{{ number_format((float) $item->line_total, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="right">Subtotal:</td>
            <td class="right">{{ number_format((float) $invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="right">Tax:</td>
            <td class="right">{{ number_format((float) $invoice->tax, 2) }}</td>
        </tr>
        <tr>
            <td class="right"><strong>Total:</strong></td>
            <td class="right"><strong>{{ number_format((float) $invoice->total, 2) }}</strong></td>
        </tr>
    </table>
</body>
</html>
