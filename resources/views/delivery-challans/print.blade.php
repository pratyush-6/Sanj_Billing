<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $challan->challan_number }}</title>
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
    </style>
</head>
<body>
    <div class="header">
        <div class="company">
            <h1>{{ $challan->company->name }}</h1>
            <p class="muted">
                {{ $challan->company->address }}@if($challan->company->address), @endif{{ $challan->company->city }} {{ $challan->company->state }} {{ $challan->company->pincode }}<br>
                @if ($challan->company->gstin) GSTIN: {{ $challan->company->gstin }}<br> @endif
                @if ($challan->company->email) {{ $challan->company->email }} @endif
                @if ($challan->company->phone) &middot; {{ $challan->company->phone }} @endif
            </p>
        </div>
        <div class="meta">
            <h2>DELIVERY CHALLAN</h2>
            <p class="muted">
                Challan #: <strong>{{ $challan->challan_number }}</strong><br>
                Date: {{ $challan->challan_date->format('d-M-Y') }}<br>
            </p>
        </div>
    </div>

    <div class="parties">
        <div class="party">
            <div class="label">Deliver To</div>
            <strong>{{ $challan->party->name }}</strong><br>
            {{ $challan->party->address }}<br>
            {{ $challan->party->city }} {{ $challan->party->state }} {{ $challan->party->pincode }}<br>
            @if ($challan->party->gstin) GSTIN: {{ $challan->party->gstin }} @endif
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU</th>
                <th class="numeric">Quantity</th>
                <th>Notes</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($challan->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->product->sku }}</td>
                    <td class="numeric">{{ number_format($item->quantity, 2) }}</td>
                    <td>{{ $item->notes ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if ($challan->notes)
        <p class="muted">Notes: {{ $challan->notes }}</p>
    @endif

    <p class="muted" style="margin-top: 40px;">This is a delivery note. Goods delivered as above, subject to invoicing.</p>
</body>
</html>
