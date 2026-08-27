<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1e293b; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        p.meta { color: #64748b; margin-top: 0; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; }
        th { background: #f8fafc; text-transform: uppercase; font-size: 10px; color: #64748b; }
        td.numeric, th.numeric { text-align: right; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <p class="meta">{{ current_company()?->name }} &middot; Generated {{ now()->format('d-M-Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                @foreach ($headings as $index => $heading)
                    <th @class(['numeric' => $index > 0])>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                <tr>
                    @foreach ($row as $index => $value)
                        <td @class(['numeric' => $index > 0])>{{ is_numeric($value) ? number_format((float) $value, 2) : $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
