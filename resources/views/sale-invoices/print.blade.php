<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        h2 { font-size: 13px; margin: 0 0 10px; color: #334155; }
        .header { display: table; width: 100%; margin-bottom: 16px; }
        .header .company { display: table-cell; width: 60%; vertical-align: top; }
        .header .meta { display: table-cell; width: 40%; vertical-align: top; text-align: right; }
        .muted { color: #64748b; }
        .parties { display: table; width: 100%; margin-bottom: 16px; }
        .parties .party { display: table-cell; width: 50%; vertical-align: top; }
        .parties .label { text-transform: uppercase; font-size: 9px; color: #64748b; margin-bottom: 3px; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        table.items th, table.items td { border: 1px solid #e2e8f0; padding: 5px 6px; }
        table.items th { background: #f8fafc; text-transform: uppercase; font-size: 9px; color: #64748b; text-align: left; }
        table.items td.numeric, table.items th.numeric { text-align: right; }
        table.totals { width: 40%; margin-left: 60%; border-collapse: collapse; }
        table.totals td { padding: 4px 6px; }
        table.totals td.numeric { text-align: right; }
        table.totals tr.grand-total td { border-top: 1px solid #1e293b; font-weight: bold; padding-top: 6px; }
        .status { display: inline-block; padding: 2px 10px; border-radius: 10px; font-size: 10px; font-weight: bold; }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-due { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">
            <h1>{{ $invoice->company->name }}</h1>
            <p class="muted">
                {{ $invoice->company->address }}@if($invoice->company->address), @endif{{ $invoice->company->city }} {{ $invoice->company->state }} {{ $invoice->company->pincode }}<br>
                @if ($invoice->company->gstin) GSTIN: {{ $invoice->company->gstin }}<br> @endif
                @if ($invoice->company->email) {{ $invoice->company->email }} @endif
                @if ($invoice->company->phone) &middot; {{ $invoice->company->phone }} @endif
            </p>
        </div>
        <div class="meta">
            <h2>SALE INVOICE</h2>
            <p class="muted">
                Invoice #: <strong>{{ $invoice->invoice_number }}</strong><br>
                Date: {{ $invoice->invoice_date->format('d-M-Y') }}<br>
                @if ($invoice->due_date) Due: {{ $invoice->due_date->format('d-M-Y') }}<br> @endif
                <span class="status {{ $invoice->amountDue() > 0 ? 'status-due' : 'status-paid' }}">
                    {{ $invoice->amountDue() > 0 ? 'DUE: '.number_format($invoice->amountDue(), 2) : 'PAID' }}
                </span>
            </p>
        </div>
    </div>

    <div class="parties">
        <div class="party">
            <div class="label">Bill To</div>
            <strong>{{ $invoice->party->name }}</strong><br>
            {{ $invoice->party->address }}<br>
            {{ $invoice->party->city }} {{ $invoice->party->state }} {{ $invoice->party->pincode }}<br>
            @if ($invoice->party->gstin) GSTIN: {{ $invoice->party->gstin }} @endif
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Product</th>
                <th>HSN</th>
                <th class="numeric">Qty</th>
                <th class="numeric">Rate</th>
                <th class="numeric">Taxable</th>
                <th class="numeric">CGST</th>
                <th class="numeric">SGST</th>
                <th class="numeric">IGST</th>
                <th class="numeric">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->hsn_code ?? '—' }}</td>
                    <td class="numeric">{{ number_format($item->quantity, 2) }}</td>
                    <td class="numeric">{{ number_format($item->unit_price, 2) }}</td>
                    <td class="numeric">{{ number_format($item->taxable_amount, 2) }}</td>
                    <td class="numeric">{{ number_format($item->cgst_amount, 2) }}</td>
                    <td class="numeric">{{ number_format($item->sgst_amount, 2) }}</td>
                    <td class="numeric">{{ number_format($item->igst_amount, 2) }}</td>
                    <td class="numeric">{{ number_format($item->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Taxable Amount</td>
            <td class="numeric">{{ number_format($invoice->taxable_amount, 2) }}</td>
        </tr>
        @if ($invoice->cgst_amount > 0 || $invoice->sgst_amount > 0)
            <tr>
                <td>CGST + SGST</td>
                <td class="numeric">{{ number_format($invoice->cgst_amount + $invoice->sgst_amount, 2) }}</td>
            </tr>
        @endif
        @if ($invoice->igst_amount > 0)
            <tr>
                <td>IGST</td>
                <td class="numeric">{{ number_format($invoice->igst_amount, 2) }}</td>
            </tr>
        @endif
        @if ($invoice->tds_amount > 0)
            <tr>
                <td>TDS ({{ $invoice->tdsSection?->section }} @ {{ $invoice->tdsSection?->rate }}%)</td>
                <td class="numeric">−{{ number_format($invoice->tds_amount, 2) }}</td>
            </tr>
        @endif
        <tr class="grand-total">
            <td>Total</td>
            <td class="numeric">{{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
    </table>

    @if ($invoice->notes)
        <p class="muted">Notes: {{ $invoice->notes }}</p>
    @endif
</body>
</html>
